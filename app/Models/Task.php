<?php

declare(strict_types=1);

namespace App\Models;

use App\Contracts\KeyValueOptionsContract;
use App\Filament\Resources\ContractResource;
use App\Traits\HasActiveScopeTrait;
use App\Traits\HasCurrentUserScopeTrait;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read User $user
 */
class Task extends Model implements KeyValueOptionsContract
{
    use HasActiveScopeTrait, HasCurrentUserScopeTrait, HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function taskHours(): HasMany
    {
        return $this->hasMany(TaskHour::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /** @return array<int, string> */
    public static function getOptions(): array
    {
        return self::currentUser()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * Get form
     *
     * @return array<int, mixed>
     */
    public static function getForm(): array
    {
        return [
            Forms\Components\Grid::make()
                ->columns(1)
                ->schema([
                    Select::make('contract_id')
                        ->label(trans('base.contract'))
                        ->relationship(
                            name: 'contract',
                            titleAttribute: 'name',
                            modifyQueryUsing: function (Builder $query, ?Task $record): void {
                                $query->where('user_id', auth()->id())
                                    ->when(! $record, fn (Builder $query) => $query->where('active', true))
                                    ->orderBy('name');
                            }
                        )
                        ->createOptionModalHeading(trans('base.create_contract'))
                        ->createOptionForm(Contract::getForm())
                        ->createOptionUsing(function (array $data): void {
                            ContractResource::createRecordForCurrentUser($data);
                        })
                        ->createOptionAction(fn (Action $action) => $action->slideOver())
                        ->default(function (?Task $record): int {
                            return key(Contract::where('user_id', auth()->id())
                                ->when(! $record, fn (Builder $query) => $query->where('active', true))
                                ->pluck('name', 'id')
                                ->all()
                            );
                        })
                        ->selectablePlaceholder(false)
                        ->required(),

                    Forms\Components\TextInput::make('name')
                        ->label(trans('base.task_name'))
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),

                    Forms\Components\TextInput::make('url')
                        ->label('URL')
                        ->maxLength(255)
                        ->default(null)
                        ->rule('url'),

                    Forms\Components\Textarea::make('note')
                        ->label(trans('base.note'))
                        ->maxLength(255)
                        ->default(null),

                    Forms\Components\Toggle::make('active')
                        ->label(trans('base.active'))
                        ->required()
                        ->default(true),
                ]),
        ];
    }
}
