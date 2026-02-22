<?php

namespace App\Services;

/**
 * ThermalPrinterService
 * ESC/POS commands for thermal receipt printers (80mm)
 */
class ThermalPrinterService
{
    // ESC/POS Commands
    const ESC = "\x1B";
    const GS = "\x1D";
    const LF = "\x0A";
    const CUT = "\x1D\x56\x00"; // Full cut
    const PARTIAL_CUT = "\x1D\x56\x01"; // Partial cut

    // Text formatting
    const ALIGN_LEFT = "\x1B\x61\x00";
    const ALIGN_CENTER = "\x1B\x61\x01";
    const ALIGN_RIGHT = "\x1B\x61\x02";
    const BOLD_ON = "\x1B\x45\x01";
    const BOLD_OFF = "\x1B\x45\x00";
    const DOUBLE_HEIGHT = "\x1B\x21\x10";
    const DOUBLE_WIDTH = "\x1B\x21\x20";
    const NORMAL_SIZE = "\x1B\x21\x00";
    const UNDERLINE_ON = "\x1B\x2D\x01";
    const UNDERLINE_OFF = "\x1B\x2D\x00";

    protected string $buffer = '';
    protected int $lineWidth = 42; // 80mm printer typically has 42 chars per line

    /**
     * Initialize printer
     */
    public function init(): self
    {
        $this->buffer = self::ESC . "@"; // Initialize printer
        return $this;
    }

    /**
     * Add text
     */
    public function text(string $text): self
    {
        $this->buffer .= $text;
        return $this;
    }

    /**
     * Add line
     */
    public function line(string $text = ''): self
    {
        $this->buffer .= $text . self::LF;
        return $this;
    }

    /**
     * Center text
     */
    public function center(string $text): self
    {
        $this->buffer .= self::ALIGN_CENTER . $text . self::LF . self::ALIGN_LEFT;
        return $this;
    }

    /**
     * Bold text
     */
    public function bold(string $text): self
    {
        $this->buffer .= self::BOLD_ON . $text . self::BOLD_OFF;
        return $this;
    }

    /**
     * Double height text
     */
    public function large(string $text): self
    {
        $this->buffer .= self::DOUBLE_HEIGHT . $text . self::NORMAL_SIZE;
        return $this;
    }

    /**
     * Add separator line
     */
    public function separator(string $char = '-'): self
    {
        $this->buffer .= str_repeat($char, $this->lineWidth) . self::LF;
        return $this;
    }

    /**
     * Two column layout (for items)
     */
    public function row(string $left, string $right): self
    {
        $leftWidth = $this->lineWidth - strlen($right) - 1;
        $leftText = substr($left, 0, $leftWidth);
        $padding = $this->lineWidth - strlen($leftText) - strlen($right);
        $this->buffer .= $leftText . str_repeat(' ', max(1, $padding)) . $right . self::LF;
        return $this;
    }

    /**
     * Three column layout
     */
    public function threeColumns(string $left, string $center, string $right): self
    {
        $totalWidth = $this->lineWidth;
        $leftWidth = (int) ($totalWidth * 0.5);
        $centerWidth = (int) ($totalWidth * 0.2);
        $rightWidth = $totalWidth - $leftWidth - $centerWidth;

        $left = str_pad(substr($left, 0, $leftWidth), $leftWidth);
        $center = str_pad(substr($center, 0, $centerWidth), $centerWidth);
        $right = str_pad(substr($right, 0, $rightWidth), $rightWidth, ' ', STR_PAD_LEFT);

        $this->buffer .= $left . $center . $right . self::LF;
        return $this;
    }

    /**
     * Add empty lines
     */
    public function feed(int $lines = 1): self
    {
        $this->buffer .= str_repeat(self::LF, $lines);
        return $this;
    }

    /**
     * Cut paper
     */
    public function cut(bool $partial = false): self
    {
        $this->feed(3);
        $this->buffer .= $partial ? self::PARTIAL_CUT : self::CUT;
        return $this;
    }

    /**
     * Open cash drawer
     */
    public function openDrawer(): self
    {
        $this->buffer .= self::ESC . "p" . "\x00" . "\x19" . "\xFA";
        return $this;
    }

    /**
     * Get the buffer content
     */
    public function getOutput(): string
    {
        return $this->buffer;
    }

    /**
     * Generate receipt for POS invoice
     */
    public function generateReceipt(array $invoiceData): string
    {
        $company = $invoiceData['company'] ?? [];
        $items = $invoiceData['items'] ?? [];
        $totals = $invoiceData['totals'] ?? [];

        return $this->init()
            // Header
            ->center($this->bold($company['name'] ?? 'Twinx ERP'))
            ->center($company['address'] ?? '')
            ->center('Tel: ' . ($company['phone'] ?? ''))
            ->separator('=')

            // Invoice info
            ->row('Invoice:', $invoiceData['invoice_number'] ?? '')
            ->row('Date:', $invoiceData['date'] ?? date('Y-m-d H:i'))
            ->row('Cashier:', $invoiceData['cashier'] ?? '')
            ->separator('-')

            // Items header
            ->threeColumns('Item', 'Qty', 'Total')
            ->separator('-')

            // Items (will be added dynamically)
            ->addItems($items)

            // Totals
            ->separator('-')
            ->row('Subtotal:', number_format($totals['subtotal'] ?? 0, 2))
            ->row('Tax:', number_format($totals['tax'] ?? 0, 2))
            ->row($this->bold('TOTAL:'), $this->bold(number_format($totals['total'] ?? 0, 2)))
            ->separator('=')

            // Payment
            ->row('Paid:', number_format($totals['paid'] ?? 0, 2))
            ->row('Change:', number_format($totals['change'] ?? 0, 2))
            ->feed(2)

            // Footer
            ->center('Thank you for your purchase!')
            ->center($company['footer'] ?? '')
            ->cut()
            ->getOutput();
    }

    /**
     * Add items to receipt
     */
    protected function addItems(array $items): self
    {
        foreach ($items as $item) {
            $name = $item['name'] ?? 'Item';
            $qty = $item['quantity'] ?? 1;
            $total = number_format($item['total'] ?? 0, 2);
            $this->threeColumns($name, (string) $qty, $total);
        }
        return $this;
    }
}
