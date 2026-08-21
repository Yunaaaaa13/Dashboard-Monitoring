<?php

namespace App\Services\Import;

use App\Services\Normalization\DateNormalizer;
use App\Services\Normalization\QuantityNormalizer;
use App\Services\Normalization\CurrencyNormalizer;

class GenericValidationService
{
    /**
     * Validate parsed rows before database import.
     *
     * @param array $rows
     * @param string $documentType MASTER_PO, INCOMING, etc.
     * @param int|null $documentId
     * @return array
     */
    public function validateRows(array $rows, string $documentType, ?int $documentId = null): array
    {
        $result = [
            'valid_rows' => [],
            'warning_rows' => [],
            'error_rows' => [],
            'issues' => [],
            'summary' => [
                'total' => count($rows),
                'valid' => 0,
                'warnings' => 0,
                'errors' => 0,
            ],
        ];

        foreach ($rows as $index => $row) {
            $rowNumber = $row['row_number'] ?? ($index + 1);
            $rowIssues = [];
            $status = 'VALID';

            // Base validation for all types
            if (isset($row['date']) && !DateNormalizer::isValidDate($row['date'])) {
                $rowIssues[] = $this->createIssue($rowNumber, 'date', 'INVALID_DATE_FORMAT', 'ERROR', 'Date format is invalid or unparseable', $row['date']);
                $status = 'ERROR';
            }

            // Type-specific validations
            if ($documentType === 'MASTER_PO') {
                $status = $this->validateMasterPo($row, $rowNumber, $rowIssues, $status);
            } elseif ($documentType === 'INCOMING') {
                $status = $this->validateIncoming($row, $rowNumber, $rowIssues, $status);
            }

            // Update row array with classification
            $row['validation_status'] = $status;

            if ($status === 'ERROR') {
                $result['error_rows'][] = $row;
                $result['summary']['errors']++;
            } elseif ($status === 'WARNING') {
                $result['warning_rows'][] = $row;
                $result['summary']['warnings']++;
            } else {
                $result['valid_rows'][] = $row;
                $result['summary']['valid']++;
            }

            if (!empty($rowIssues)) {
                $result['issues'] = array_merge($result['issues'], $rowIssues);
            }
        }

        return $result;
    }

    protected function validateMasterPo(array $row, int $rowNumber, array &$issues, string $status): string
    {
        if (empty($row['item_code'])) {
            $issues[] = $this->createIssue($rowNumber, 'item_code', 'MISSING_ITEM_CODE', 'ERROR', 'Item code is required', null);
            $status = 'ERROR';
        }

        if (!isset($row['qty']) || QuantityNormalizer::normalize($row['qty']) <= 0) {
            $issues[] = $this->createIssue($rowNumber, 'qty', 'INVALID_QTY', 'ERROR', 'Quantity must be a valid number greater than zero', $row['qty'] ?? null);
            $status = 'ERROR';
        }

        if (empty($row['delivery_date']) || !DateNormalizer::isValidDate($row['delivery_date'])) {
            $issues[] = $this->createIssue($rowNumber, 'delivery_date', 'INVALID_DELIVERY_DATE', 'ERROR', 'Delivery date is required and must be valid', $row['delivery_date'] ?? null);
            $status = 'ERROR';
        }

        if (empty($row['supplier'])) {
            $issues[] = $this->createIssue($rowNumber, 'supplier', 'MISSING_SUPPLIER', 'WARNING', 'Supplier name is missing', null);
            $status = $status === 'ERROR' ? 'ERROR' : 'WARNING';
        }

        return $status;
    }

    protected function validateIncoming(array $row, int $rowNumber, array &$issues, string $status): string
    {
        if (empty($row['item_code'])) {
            $issues[] = $this->createIssue($rowNumber, 'item_code', 'MISSING_ITEM_CODE', 'ERROR', 'Item code is required', null);
            $status = 'ERROR';
        }

        // actual_received can be >= 0 (0 means open PO not received yet / full outstanding)
        if (isset($row['actual_received']) && is_numeric($row['actual_received']) && (float)$row['actual_received'] < 0) {
            $issues[] = $this->createIssue($rowNumber, 'actual_received', 'INVALID_QTY', 'ERROR', 'Actual received quantity cannot be negative', $row['actual_received'] ?? null);
            $status = 'ERROR';
        } elseif (!isset($row['actual_received']) && !isset($row['target_order']) && !isset($row['plan_qty'])) {
            $issues[] = $this->createIssue($rowNumber, 'actual_received', 'MISSING_QTY', 'ERROR', 'Quantity or target order is required', null);
            $status = 'ERROR';
        }

        return $status;
    }

    protected function createIssue(int $rowNum, string $col, string $type, string $severity, string $msg, $val): array
    {
        return [
            'row_number' => $rowNum,
            'column_name' => $col,
            'issue_type' => $type,
            'severity' => $severity,
            'message' => $msg,
            'raw_value' => is_scalar($val) ? (string)$val : json_encode($val),
        ];
    }
}
