<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('pdf.report.title') }}</title>
    <style>
        /* Adjusted styles with minimized margins and proper page handling */
        body {
            font-family: Arial, sans-serif;
            font-size: 12px; /* Base font size for the document */
            margin: 5px; /* Reduced margins around the page */
        }

        .header {
            text-align: center;
            font-size: 18px; /* Slightly smaller header */
            margin-bottom: 10px; /* Reduced space below the header */
        }

        .table-container {
            page-break-inside: avoid; /* Prevent table from breaking across pages */
            margin-bottom: 10px; /* Slightly smaller space between tables */
        }

        .new-day-header {
            page-break-before: avoid; /* Prevent header from being isolated at page end */
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px; /* Reduced space above the new day header */
            margin-bottom: 5px; /* Reduced space between header and table */
        }

        .table {
            width: 100%; /* Ensures table takes full width of the page */
            border-collapse: collapse;
            margin-top: 5px; /* Reduced space between header and table start */
            table-layout: fixed; /* Enforces fixed column widths */
        }

        .table th, .table td {
            font-size: 12px; /* Table font size for readability */
            border: 1px solid #ddd;
            padding: 6px; /* Reduced padding to minimize overall size */
            text-align: left;
            word-wrap: break-word; /* Allows content to wrap within cells */
        }

        .table th {
            background-color: #f4f4f4; /* Light background for headers */
        }

        .table th:first-child, .table td:first-child {
            width: 70%; /* First column for task name */
        }

        .table th:nth-child(2), .table td:nth-child(2) {
            width: 10%; /* Second column for hours */
        }

        .table th:nth-child(3), .table td:nth-child(3) {
            width: 20%; /* Third column for comments */
        }

        .task-name {
            font-size: 12px; /* Consistent with table font size */
        }

        .comment {
            font-style: italic;
            font-size: 9px !important; /* Slightly smaller for comments */
            color: gray;
        }
    </style>
</head>

<body>
<div class="header">
    {{ __('pdf.report.title') }} {{ $year }}/{{ $month }}<br>
    <span style="font-size: 10px;">{{ data_get($contract, 'customer.name') }}</span>
</div>

@foreach ($content as $date => $tasks)
    <div class="table-container">
        <div class="new-day-header">{{ $date }}</div>
        <table class="table">
            <thead>
            <tr>
                <th>{{ __('base.task') }}</th>
                <th>{{ __('base.hours') }}</th>
                <th>{{ __('base.comment') }}</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($tasks as $task)
                <tr>
                    <td>
                        @if (!empty($task['url']))
                            <a href="{{ $task['url'] }}" target="_blank" class="task-name">{{ $task['name'] }}</a>
                        @else
                            <span class="task-name">{{ $task['name'] }}</span>
                        @endif
                    </td>
                    <td>{{ number_format($task['hours'], 1) }}</td>
                    <td class="comment">{{ $task['comment'] }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th>{{ strtoupper(__('pdf.report.total')) }}</th>
                <th>{{ number_format(collect($tasks)->sum('hours'), 1) }}</th>
                <th></th>
            </tr>
            </tfoot>
        </table>
    </div>
@endforeach
</body>
</html>
