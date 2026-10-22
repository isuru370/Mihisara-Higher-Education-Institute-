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
    
        /* ---------- Fast payment screen ---------- */

        body.np-has-pay-bar #student-section { padding-bottom: 110px; }

        .np-shortcut-strip { display: flex; flex-wrap: wrap; gap: .4rem .9rem; font-size: .85rem; color: #6c757d; }
        .np-shortcut-strip span { white-space: nowrap; }

        kbd, .np-kbd-light {
            display: inline-block; min-width: 1.6em; padding: .1rem .4rem; border-radius: .35rem;
            font-size: .78em; font-weight: 700; text-align: center; line-height: 1.4;
            font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
        }
        kbd { background: #212529; color: #fff; border-bottom: 2px solid #000; }
        .np-kbd-light { background: rgba(255,255,255,.22); color: inherit; border: 1px solid rgba(255,255,255,.45); }

        .np-shortcut-table td { padding: .45rem .5rem; vertical-align: middle; }
        .np-shortcut-table td:first-child { white-space: nowrap; width: 140px; }

        .np-search-input { font-size: 1.6rem !important; font-weight: 700; letter-spacing: .06em; height: 64px; text-transform: uppercase; }
        .np-search-group .btn { font-size: 1.15rem; font-weight: 600; padding-left: 1.5rem; padding-right: 1.5rem; }

        .np-stat { border-radius: .75rem; padding: .6rem 1rem; background: #f8f9fa; min-width: 130px; }
        .np-stat-danger { background: #fdecea; color: #b02a37; }
        .np-stat-primary { background: #e7f1ff; color: #0a58ca; }
        .np-stat-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .05em; opacity: .85; }
        .np-stat-value { font-size: 1.5rem; font-weight: 800; line-height: 1.2; }

        /* Class cards */
        .np-card { cursor: pointer; position: relative; transition: box-shadow .15s, border-color .15s, transform .1s; border: 2px solid transparent; }
        .np-card:hover { transform: translateY(-1px); }
        .np-card:focus-visible { outline: 3px solid #86b7fe; outline-offset: 2px; }
        .np-card-selected { border-color: #198754 !important; box-shadow: 0 0 0 4px rgba(25,135,84,.18) !important; background: #f3fbf6; }
        .np-card-disabled { cursor: default; opacity: .75; }
        .np-card-disabled:hover { transform: none; }
        .np-card-number {
            flex: 0 0 auto; width: 40px; height: 40px; border-radius: .6rem; background: #212529; color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 800;
        }
        .np-card-selected .np-card-number { background: #198754; }
        .np-card-disabled .np-card-number { background: #adb5bd; }
        .np-card-check { width: 1.6rem; height: 1.6rem; flex: 0 0 auto; pointer-events: none; }
        .np-card .pay-single-btn { padding-top: .75rem; padding-bottom: .75rem; }

        .payment-status-free {
            display: inline-block; padding: .3rem .7rem; border-radius: 999px; font-size: .72rem; font-weight: 700;
            background: #e0f2fe; color: #0369a1; white-space: nowrap;
        }

        /* Sticky pay bar */
        .np-pay-bar {
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 1030;
            background: #1f2937; color: #fff; padding: .85rem 1.25rem; box-shadow: 0 -6px 20px rgba(0,0,0,.2);
        }
        .np-pay-bar .np-pay-total { font-size: 1.9rem; font-weight: 800; line-height: 1.1; }
        .np-pay-btn { font-size: 1.35rem !important; font-weight: 800; padding: .85rem 2.25rem !important; min-width: 240px; }

        /* Payment modal */
        .np-modal-total { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: .9rem; padding: 1rem 1.25rem; }
        .np-modal-amount { font-size: 2.6rem; font-weight: 800; color: #15803d; line-height: 1.1; }
        .np-method-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: .6rem; }
        .np-method {
            border: 2px solid #dee2e6; background: #fff; border-radius: .75rem; padding: .75rem .5rem;
            font-weight: 600; text-align: center; position: relative; transition: all .12s;
        }
        .np-method:hover { border-color: #86b7fe; }
        .np-method.active { border-color: #198754; background: #198754; color: #fff; }
        .np-method-icon { display: block; font-size: 1.5rem; margin-bottom: .15rem; }
        .np-method kbd { position: absolute; top: .3rem; right: .35rem; font-size: .65rem; opacity: .8; }
        #confirm-payment-btn { font-size: 1.3rem; font-weight: 800; padding-top: .9rem; padding-bottom: .9rem; }

        @media (max-width: 576px) {
            .np-method-grid { grid-template-columns: repeat(2, 1fr); }
            .np-pay-btn { min-width: 0; width: 100%; }
            .np-modal-amount { font-size: 2rem; }
            .np-shortcut-strip { display: none; }
        }
    </style>
@endpush
