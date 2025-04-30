<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;

class GeneratorService
{
    /**
     * Generates a file name by concatenating the provided title array into a string,
     * converting it into a slug format, and appending the specified suffix.
     *
     * @param array $title An array of strings that will be combined to form the base name.
     * @param string $suffix The file extension or suffix to append to the generated name. Default is '.pdf'.
     * @return string The generated file name in slug format with the specified suffix.
     */
    public static function generateFileName(array $title, string $suffix = '.pdf'): string
    {
        return str()->slug(implode(' ', $title)) . $suffix;
    }

    /**
     * Extracts the initials from the given name.
     *
     * @param string $name The input name from which initials will be derived.
     * @return string The uppercase initials extracted from the provided name.
     */
    public static function getInitials(string $name): string
    {
        preg_match_all('/\b\w/u', $name, $matches);

        return strtoupper(implode('', $matches[0]));
    }

    /**
     * Generates the next invoice number by incrementing the numeric part of the given invoice number.
     *
     * @param string $invoiceNumber The current invoice number which ends with a numeric part.
     * @return string|null The next invoice number with the numeric part incremented; null if no numeric part is found.
     */
    public static function getNextInvoiceNumber(string $invoiceNumber): ?string
    {
        // Extract the last numeric portion of the invoice number
        preg_match('/(\d+)$/', $invoiceNumber, $matches);

        if (!empty($matches[1])) {
            // Increment the numeric part by 1
            $newNumber = (int) $matches[1] + 1;

            // Pad the number with leading zeroes to maintain the format (e.g., 004)
            $newNumberPadded = str_pad((string) $newNumber, strlen($matches[1]), '0', STR_PAD_LEFT);

            // Replace the old number with the new one in the string
            return Str::replaceLast($matches[1], $newNumberPadded, $invoiceNumber);
        }

        return null;
    }

}
