<?php

declare(strict_types=1);

use App\Services\GeneratorService;

describe('getNextInvoiceNumber', function() {
    it('should return the next invoice number with plain number input', function() {
        expect(GeneratorService::getNextInvoiceNumber('1'))->toBe('2');
    });

    it('should return the next invoice number with combined text and number input', function() {
        expect(GeneratorService::getNextInvoiceNumber('ABCD-1234-1'))->toBe('ABCD-1234-2');
    });

    it('should return null on plain text', function() {
        expect(GeneratorService::getNextInvoiceNumber('ABCD'))->toBeNull();
    });

    it('should return null on combined input but with text suffix', function() {
        expect(GeneratorService::getNextInvoiceNumber('ABCD-1234-q'))->toBeNull();
    });
});
