<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public $paymentData;   // flat student payments array
    public $pdfContent;    // PDF binary
    public $fileName;      // PDF filename
    public $teacherId;
    public $month;
    public $teacherName;
    public $teacherEmail;
    public $totalAmount;
    public $teacherAmount;
    public $totalStudents;
    public $totalClasses;

    /**
     * Constructor
     *
     * @param array $paymentData  Flat student payment data + teacher info
     * @param string $pdfContent  PDF binary content
     * @param string $fileName    PDF file name
     * @param int $teacherId
     * @param string $month       Month display (e.g., January-2025)
     */
    public function __construct($paymentData, $pdfContent, $fileName, $teacherId, $month)
    {
        $this->paymentData = $paymentData;
        $this->pdfContent = $pdfContent;
        $this->fileName = $fileName;
        $this->teacherId = $teacherId;
        $this->month = $month;

        // Teacher info
        $this->teacherName = $paymentData['teacher']['name'] ?? 'Unknown';
        $this->teacherEmail = $paymentData['teacher']['email'] ?? null;

        // Totals calculation
        $this->totalAmount = collect($paymentData['students'])->sum('amount');
        $teacherPercentage = $paymentData['teacher']['percentage'] ?? 0;
        $this->teacherAmount = round($this->totalAmount * ($teacherPercentage / 100), 2);

        // Total students (unique)
        $this->totalStudents = collect($paymentData['students'])->pluck('student_id')->unique()->count();

        // Total classes (unique student_classes_id if exists)
        $this->totalClasses = collect($paymentData['students'])->pluck('student_class_id')->unique()->count();
    }

    /**
     * Build the message
     *
     * @return $this
     */
    public function build(): self
    {
        return $this->subject('Student Payment Report - ' . $this->month)
            ->view('emails.payment_notification')   // email Blade එක
            ->with([
                'month' => $this->month,
                'teacherName' => $this->teacherName,
                'teacherId' => $this->teacherId,
                'paymentData' => $this->paymentData,
                'totalStudents' => count($this->paymentData['students'] ?? []),
                'totalAmount' => $this->totalAmount,
                'teacherAmount' => $this->teacherAmount,
                'totalClasses' => $this->totalClasses ?? 0,
            ])
            ->attachData($this->pdfContent, $this->fileName, [
                'mime' => 'application/pdf',
            ]);
    }
}
