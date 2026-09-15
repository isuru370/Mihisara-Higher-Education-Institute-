@push('styles')
    <style>
        #qr-reader {
            width: 100%;
            max-width: 360px;
            margin: 0 auto
        }

        #qr-reader video {
            max-height: 230px;
            object-fit: cover;
            border-radius: 10px
        }

        #qr-reader #qr-reader__scan_region {
            min-height: 180px
        }

        .table td {
            vertical-align: middle
        }

        .payment-status-paid {
            color: #10b981;
            font-weight: 700;
            background: #ecfdf5;
            padding: 4px 14px;
            border-radius: 20px;
            display: inline-block;
            font-size: .75rem
        }

        .payment-status-unpaid {
            color: #ef4444;
            font-weight: 700;
            background: #fef2f2;
            padding: 4px 14px;
            border-radius: 20px;
            display: inline-block;
            font-size: .75rem
        }

        .receipt-paper {
            width: 58mm;
            margin: auto;
            padding: 4mm;
            background: #fff;
            color: #000;
            font-family: monospace;
            font-size: 11px;
            line-height: 1.45
        }

        .receipt-line {
            border-top: 1px dashed #000;
            margin: 7px 0
        }

        .receipt-summary {
            line-height: 1.6
        }

        .receipt-item {
            line-height: 1.45;
            word-break: break-word
        }

        .receipt-total {
            font-size: 14px;
            font-weight: 700
        }

        .text-center {
            text-align: center
        }

        .modal-content {
            border-radius: 20px;
            border: none;
            box-shadow: 0 20px 60px rgba(0, 0, 0, .15)
        }

        .modal-header {
            padding: 1.25rem 1.5rem
        }

        .modal-body {
            padding: 1.5rem
        }

        .modal-footer {
            padding: 1rem 1.5rem
        }

        .class-payment-card {
            height: 100%;
            background: #fff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease
        }

        .class-payment-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, .08);
            border-color: #d9dee3
        }

        .class-payment-card-header {
            padding-bottom: 15px;
            border-bottom: 1px solid #f0f1f3
        }

        .class-payment-title {
            font-size: 15px;
            font-weight: 700;
            color: #212529;
            line-height: 1.3
        }

        .class-payment-subject {
            font-size: 12px;
            color: #6c757d
        }

        .class-payment-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            padding: 16px 0
        }

        .class-payment-detail {
            min-width: 0
        }

        .class-payment-detail-label {
            font-size: 11px;
            color: #8a9199;
            margin-bottom: 3px
        }

        .class-payment-detail-value {
            font-size: 13px;
            font-weight: 600;
            color: #343a40;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis
        }

        .class-payment-fee {
            font-size: 14px;
            font-weight: 700
        }

        .last-payment-box {
            background: #f8f9fa;
            border: 1px solid #eef0f2;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 15px
        }

        .last-payment-header {
            margin-bottom: 8px
        }

        .last-payment-label {
            font-size: 11px;
            font-weight: 600;
            color: #6c757d
        }

        .last-payment-content {
            display: flex;
            justify-content: space-between;
            align-items: center
        }

        .last-payment-content strong {
            font-size: 13px
        }

        .last-payment-receipt {
            margin-top: 8px;
            padding-top: 7px;
            border-top: 1px dashed #dee2e6;
            font-size: 10px;
            color: #8a9199
        }

        .last-payment-empty {
            opacity: .75
        }

        .class-payment-card-footer {
            margin-top: auto;
            padding-top: 13px;
            border-top: 1px solid #f0f1f3;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px
        }

        .class-payment-card-footer .btn {
            min-width: 75px
        }

        .class-payment-card .class-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer
        }

        .classes-empty-state {
            min-height: 220px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center
        }

        .classes-empty-icon {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: #f1f3f5;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            font-size: 26px;
            color: #adb5bd
        }

        @media(max-width:767px) {
            .card-header .d-flex {
                flex-wrap: wrap
            }

            .class-payment-details {
                grid-template-columns: 1fr 1fr
            }
        }

        @media(max-width:575px) {
            .class-payment-details {
                grid-template-columns: 1fr
            }
        }
    </style>
@endpush
