<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Administrator</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --primary: #2563eb;
            --primary-2: #3b82f6;
            --dark-1: #020617;
            --dark-2: #0f172a;
            --dark-3: #1e3a8a;
            --panel: rgba(255, 255, 255, .08);
            --panel-border: rgba(255, 255, 255, .12);
            --text-soft: rgba(255, 255, 255, .72);
            --muted: #728097;
            --border: #dfe6f2;
            --success: #10b981;
            --whatsapp: #22c55e;
            --facebook: #1877f2;
            --email: #7c3aed;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", sans-serif;
            background:
                radial-gradient(circle at top left, rgba(37, 99, 235, .22), transparent 35%),
                radial-gradient(circle at bottom right, rgba(59, 130, 246, .16), transparent 35%),
                linear-gradient(135deg, var(--dark-1), var(--dark-2), var(--dark-3));
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
            position: relative;
        }

        .floating-bg {
            position: absolute;
            inset: 0;
            overflow: hidden;
        }

        .floating-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .05);
            animation: float 10s infinite ease-in-out;
        }

        .floating-circle:nth-child(1) {
            width: 220px;
            height: 220px;
            top: -60px;
            left: -60px;
        }

        .floating-circle:nth-child(2) {
            width: 170px;
            height: 170px;
            bottom: -50px;
            right: -30px;
            animation-delay: 2s;
        }

        .floating-circle:nth-child(3) {
            width: 120px;
            height: 120px;
            top: 50%;
            left: 12%;
            animation-delay: 1s;
        }

        .floating-circle:nth-child(4) {
            width: 90px;
            height: 90px;
            top: 18%;
            right: 18%;
            animation-delay: 3s;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-22px);
            }

            100% {
                transform: translateY(0px);
            }
        }

        .contact-shell {
            width: 100%;
            max-width: 1180px;
            position: relative;
            z-index: 5;
        }

        .glass-card {
            border-radius: 34px;
            overflow: hidden;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            backdrop-filter: blur(22px);
            box-shadow:
                0 20px 50px rgba(0, 0, 0, .45),
                inset 0 1px 0 rgba(255, 255, 255, .08);
        }

        .left-panel {
            position: relative;
            color: #fff;
            padding: 32px 24px;
            background:
                linear-gradient(180deg, rgba(15, 23, 42, .92), rgba(30, 41, 59, .90));
        }

        .left-panel::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 0;
            height: 0;
            border-top: 120px solid rgba(255, 255, 255, .06);
            border-left: 120px solid transparent;
        }

        .profile-wrap {
            text-align: center;
        }

        .profile-image-wrapper {
            position: relative;
            width: 130px;
            height: 130px;
            margin: 0 auto 18px;
        }

        .profile-image {
            width: 130px;
            height: 130px;
            border-radius: 50%;
            object-fit: cover;
            object-position: center 5%;
            border: 4px solid rgba(255, 255, 255, .18);
            box-shadow: 0 18px 35px rgba(0, 0, 0, .28);
            background: #fff;
        }

        .online-badge {
            position: absolute;
            bottom: 8px;
            right: 8px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--success);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #1e293b;
            color: #fff;
            font-size: 11px;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 4px;
            line-height: 1.2;
        }

        .profile-role {
            font-size: 13px;
            color: rgba(255, 255, 255, .74);
            margin-bottom: 14px;
        }

        .profile-description {
            margin-top: 14px;
            font-size: 13px;
            line-height: 1.7;
            color: rgba(255, 255, 255, .74);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: .6rem 1rem;
            border-radius: 999px;
            background: rgba(255, 255, 255, .10);
            border: 1px solid rgba(255, 255, 255, .08);
            color: #fff;
            font-weight: 600;
            font-size: .85rem;
        }

        .section-title {
            font-size: 14px;
            font-weight: 800;
            color: rgba(255, 255, 255, .95);
            display: flex;
            align-items: center;
            gap: 8px;
            margin: 22px 0 12px;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, .14);
            margin: 10px 0 14px;
        }

        .info-card {
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 14px;
            padding: 12px 14px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(180deg, #8f7ce8, #6e61d9);
            flex: 0 0 40px;
        }

        .info-icon i {
            font-size: 14px;
            color: #fff;
        }

        .info-label {
            font-size: 11px;
            color: rgba(255, 255, 255, .68);
            margin-bottom: 2px;
        }

        .info-value {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            line-height: 1.3;
        }

        .hours-box {
            display: flex;
            gap: 10px;
            margin-top: 12px;
        }

        .hour-card {
            flex: 1;
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            min-height: 62px;
        }

        .hour-title {
            font-size: 11px;
            color: rgba(255, 255, 255, .72);
            margin-bottom: 4px;
        }

        .hour-value {
            font-size: 12px;
            font-weight: 800;
            color: #fff;
        }

        .response-pill {
            margin-top: 16px;
            background: linear-gradient(90deg, #14b8a6, #22c55e);
            color: #fff;
            border-radius: 999px;
            padding: 10px 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
        }

        .social-row {
            display: flex;
            gap: 12px;
            margin-top: 18px;
            justify-content: center;
        }

        .social-btn {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: .2s ease;
            box-shadow: 0 10px 20px rgba(0, 0, 0, .18);
        }

        .social-btn:hover {
            transform: translateY(-3px);
            color: #fff;
        }

        .facebook-btn {
            background: linear-gradient(135deg, #1877f2, #2563eb);
        }

        .whatsapp-btn {
            background: linear-gradient(135deg, #22c55e, #16a34a);
        }

        .email-btn {
            background: linear-gradient(135deg, #7c3aed, #6d28d9);
        }

        .right-panel {
            background: #fff;
            padding: 30px 26px;
        }

        .form-title {
            font-size: 28px;
            font-weight: 900;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .form-label {
            font-size: 12px;
            font-weight: 800;
            color: #425468;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            border: 1px solid var(--border);
            border-radius: 14px;
            min-height: 46px;
            font-size: 13px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #8da8ff;
        }

        textarea.form-control {
            min-height: 132px;
            resize: vertical;
        }

        .input-icon {
            position: relative;
        }

        .input-icon .icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #7b8ba6;
            font-size: 13px;
            z-index: 2;
        }

        .input-icon .form-control,
        .input-icon .form-select {
            padding-left: 36px;
        }

        .field-error {
            font-size: 12px;
            color: #ef4444;
            margin-top: 4px;
        }

        .upload-box {
            border: 1.5px dashed #d7deea;
            border-radius: 14px;
            background: #fafcff;
            padding: 18px;
            text-align: center;
            transition: .2s ease;
            cursor: pointer;
        }

        .upload-box:hover {
            border-color: #9db5ff;
            background: #f8fbff;
        }

        .upload-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9f1ff;
            color: #5683f6;
            font-size: 18px;
        }

        .upload-text {
            font-size: 13px;
            color: #6b7c93;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .upload-subtext {
            font-size: 11px;
            color: #a0aec0;
        }

        .action-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        .btn-action {
            height: 44px;
            border-radius: 12px;
            border: none;
            font-size: 13px;
            font-weight: 800;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            transition: .2s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            color: #fff;
        }

        .btn-call {
            background: linear-gradient(90deg, #10b981, #0ea5a4);
        }

        .btn-whatsapp {
            background: linear-gradient(90deg, #22c55e, #16a34a);
        }

        .bottom-actions {
            display: grid;
            grid-template-columns: 1fr 1fr 1.35fr;
            gap: 10px;
        }

        .btn-soft {
            height: 42px;
            border-radius: 12px;
            border: 1px solid #d7deea;
            background: #fff;
            color: #627086;
            font-size: 13px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: .2s ease;
        }

        .btn-soft:hover {
            background: #f8fbff;
            transform: translateY(-2px);
        }

        .btn-send {
            border: none;
            color: #fff;
            background: linear-gradient(90deg, #6b5bca, #7d61d1);
            box-shadow: 0 12px 24px rgba(107, 91, 202, .22);
        }

        .btn-send:hover {
            opacity: .96;
        }

        @media (min-width: 992px) {
            .contact-shell .glass-card {
                min-height: 780px;
            }
        }

        @media (max-width: 991.98px) {
            body {
                overflow: auto;
                align-items: flex-start;
            }

            .left-panel {
                border-bottom-left-radius: 0;
                border-bottom-right-radius: 0;
            }

            .right-panel {
                padding-top: 18px;
            }
        }

        @media (max-width: 575.98px) {
            body {
                padding: 8px;
            }

            .contact-shell {
                border-radius: 16px;
            }

            .right-panel,
            .left-panel {
                padding: 18px 14px;
            }

            .profile-name {
                font-size: 20px;
            }

            .action-row,
            .bottom-actions {
                grid-template-columns: 1fr;
            }

            .hours-box {
                flex-direction: column;
            }

            .form-title {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>

    <div class="floating-bg">
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
        <div class="floating-circle"></div>
    </div>

    <div class="contact-shell">
        <div class="glass-card">
            <div class="row g-0">
                {{-- LEFT PANEL --}}
                <div class="col-lg-4 left-panel">
                    <div class="profile-wrap">
                        <div class="profile-image-wrapper">
                            <img src="{{ asset('storage/chief_administrative_officer_image/image.jpeg') }}"
                                alt="Chief Administrative Officer" class="profile-image"
                                onerror="this.onerror=null;this.src='{{ asset('storage/logo/black_logo3.png') }}'">
                            <div class="online-badge">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                        </div>

                        <div class="profile-name">Miss. Dhananjya Dharshani</div>
                        <div class="profile-role">Chief Administrative Officer</div>

                        <div class="profile-description">
                            Dedicated to supporting students, parents, and academic excellence through modern
                            educational administration and digital innovation.
                        </div>

                        <div class="social-row">
                            <a href="https://www.facebook.com/share/1DBMkR7WK5" target="_blank"
                                class="social-btn facebook-btn">
                                <i class="fa-brands fa-facebook-f"></i>
                            </a>

                            <a href="https://wa.me/94766499254" target="_blank" class="social-btn whatsapp-btn">
                                <i class="fa-brands fa-whatsapp"></i>
                            </a>

                            <a href="mailto:info@nexorait.lk" class="social-btn email-btn">
                                <i class="fa-solid fa-envelope"></i>
                            </a>
                        </div>
                    </div>

                    <div class="section-title mt-4">
                        <i class="fa-solid fa-address-book"></i>
                        Contact Information
                    </div>
                    <div class="divider"></div>

                    <div class="info-card">
                        <div class="info-icon"><i class="fa-solid fa-envelope"></i></div>
                        <div>
                            <div class="info-label">Support Email</div>
                            <div class="info-value">info@nexorait.lk</div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                        <div>
                            <div class="info-label">Hotline Number</div>
                            <div class="info-value">+94 76 89 71 213</div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon"><i class="fa-brands fa-whatsapp"></i></div>
                        <div>
                            <div class="info-label">WhatsApp</div>
                            <div class="info-value">+94 76 64 99 254</div>
                        </div>
                    </div>

                    <div class="info-card">
                        <div class="info-icon"><i class="fa-solid fa-location-dot"></i></div>
                        <div>
                            <div class="info-label">Office Address</div>
                            <div class="info-value">Mirigama, Sri Lanka</div>
                        </div>
                    </div>

                    <div class="section-title mt-4">
                        <i class="fa-solid fa-clock"></i>
                        Office Hours
                    </div>
                    <div class="divider"></div>

                    <div class="hours-box">
                        <div class="hour-card">
                            <div class="hour-title">Monday - Sunday</div>
                            <div class="hour-value">8:00 AM - 5:00 PM</div>
                        </div>
                        <div class="hour-card">
                            <div class="hour-title">Public Holidays</div>
                            <div class="hour-value">Closed</div>
                        </div>
                    </div>

                    <div class="response-pill">
                        <i class="fa-solid fa-circle-check"></i>
                        Typical response time: Within 24 hours
                    </div>
                </div>

                {{-- RIGHT PANEL --}}
                <div class="col-lg-8 right-panel">
                    <div class="form-title">
                        <i class="fa-solid fa-paper-plane me-2"></i>Send a Message
                    </div>
                    <div class="form-subtitle">
                        Fill out the form below and we’ll get back to you as soon as possible.
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('contact_administrator.send') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name *</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-user icon"></i>
                                    <input type="text" name="full_name" class="form-control"
                                        placeholder="Enter your full name" value="{{ old('full_name') }}">
                                </div>
                                @error('full_name')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address *</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-envelope icon"></i>
                                    <input type="email" name="email" class="form-control" placeholder="Enter your email"
                                        value="{{ old('email') }}">
                                </div>
                                @error('email')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Phone Number *</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-phone icon"></i>
                                    <input type="text" name="phone" class="form-control"
                                        placeholder="Enter your phone number" value="{{ old('phone') }}">
                                </div>
                                @error('phone')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Subject *</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-tag icon"></i>
                                    <select name="subject" class="form-select">
                                        <option value="">Select a subject</option>
                                        <option value="Admission" {{ old('subject') === 'Admission' ? 'selected' : '' }}>
                                            Admission</option>
                                        <option value="Course Inquiry" {{ old('subject') === 'Course Inquiry' ? 'selected' : '' }}>Course Inquiry</option>
                                        <option value="Payment Issue" {{ old('subject') === 'Payment Issue' ? 'selected' : '' }}>Payment Issue</option>
                                        <option value="Technical Support" {{ old('subject') === 'Technical Support' ? 'selected' : '' }}>Technical Support</option>
                                        <option value="Other" {{ old('subject') === 'Other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>
                                @error('subject')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Message *</label>
                                <textarea name="message" class="form-control"
                                    placeholder="Type your message here...">{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Attachment (Optional)</label>
                                <input type="file" name="attachment" class="form-control d-none" id="attachmentInput">
                                <label for="attachmentInput" class="upload-box w-100">
                                    <div class="upload-icon">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                    </div>
                                    <div class="upload-text">Click to upload or drag and drop</div>
                                    <div class="upload-subtext">Supported: PDF, DOC, DOCX, JPG, PNG (Max: 5MB)</div>
                                </label>
                                @error('attachment')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <div class="action-row">
                                    <a href="tel:+94766499254" class="btn-action btn-call">
                                        <i class="fa-solid fa-phone"></i> Call Now
                                    </a>
                                    <a href="https://wa.me/94766499254" target="_blank" class="btn-action btn-whatsapp">
                                        <i class="fa-brands fa-whatsapp"></i> WhatsApp
                                    </a>
                                </div>
                            </div>

                            <div class="col-12 mt-2">
                                <div class="bottom-actions">
                                    <button type="reset" class="btn-soft">
                                        <i class="fa-solid fa-rotate-left"></i> Reset
                                    </button>

                                    <button type="button" class="btn-soft"
                                        onclick="document.getElementById('attachmentInput').value='';">
                                        <i class="fa-solid fa-broom"></i> Clear
                                    </button>

                                    <button type="submit" class="btn-soft btn-send">
                                        <i class="fa-solid fa-paper-plane"></i> Send Message
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>

</html>