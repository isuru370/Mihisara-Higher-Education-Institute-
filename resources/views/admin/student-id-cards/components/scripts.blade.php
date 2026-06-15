<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        // ==================== GET CSRF TOKEN ====================
        let token = null;

        const metaToken = document.querySelector('meta[name="csrf-token"]');
        if (metaToken) {
            token = metaToken.getAttribute('content');
        }

        if (!token) {
            const inputToken = document.querySelector('input[name="_token"]');
            if (inputToken) {
                token = inputToken.value;
            }
        }

        if (!token) {
            token = '{{ csrf_token() }}';
        }

        console.log('CSRF Token:', token ? 'Loaded' : 'Missing');

        // ==================== CARD SCALING ====================
        function scaleCards() {
            document.querySelectorAll('.id-scale-box').forEach(box => {
                const card = box.querySelector('.student-id-card');
                if (!card) return;

                const boxW = box.clientWidth;
                const cardW = card.offsetWidth;

                if (cardW <= 0 || boxW <= 0) return;

                const scale = Math.min(boxW / cardW, 1);
                card.style.transform = `scale(${scale})`;
                card.style.transformOrigin = 'top left';
                box.style.height = (card.offsetHeight * scale) + 'px';

                const wrap = box.closest('.id-card-preview-wrap');
                if (wrap) wrap.style.height = (card.offsetHeight * scale + 24) + 'px';
            });
        }

        scaleCards();
        window.addEventListener('resize', scaleCards);

        // ==================== FIXED: PREPARE CARD FOR CAPTURE ====================
        function prepareCardForCapture(cardElement) {
            // Store original styles safely
            const originalStyles = new Map();

            // Handle gradient text elements
            const gradientElements = cardElement.querySelectorAll('.card-inst-name, .card-inst-sub');
            gradientElements.forEach(el => {
                if (el && el.style) {
                    // Store original styles safely
                    originalStyles.set(el, {
                        background: el.style.background || '',
                        webkitBackgroundClip: el.style.webkitBackgroundClip || '',
                        color: el.style.color || ''
                    });

                    // Apply solid color for capture
                    el.style.background = 'none';
                    el.style.webkitBackgroundClip = 'unset';
                    el.style.color = '#1d4ed8';
                }
            });

            return originalStyles;
        }

        // ==================== FIXED: RESTORE CARD STYLES ====================
        function restoreCardStyles(cardElement, originalStyles) {
            // Restore original styles safely
            originalStyles.forEach((styles, el) => {
                if (el && el.style) {
                    if (styles.background !== undefined) el.style.background = styles.background;
                    if (styles.webkitBackgroundClip !== undefined) el.style.webkitBackgroundClip = styles.webkitBackgroundClip;
                    if (styles.color !== undefined) el.style.color = styles.color;
                }
            });
        }

        // ==================== SINGLE DOWNLOAD ====================
        async function downloadSingleCard(cardId, studentKey, buttonElement) {
            console.log('=== SINGLE DOWNLOAD STARTED ===');

            if (!token) {
                alert('Security token not found. Please refresh the page.');
                return;
            }

            const card = document.querySelector(`.student-card[data-id="${studentKey}"]`);
            if (!card) {
                alert('Card not found');
                return;
            }

            const cardElement = card.querySelector('.student-id-card');
            if (!cardElement) {
                alert('ID card element not found');
                return;
            }

            // Show loading state
            const originalText = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
            buttonElement.disabled = true;

            // Store original styles
            let originalStyles = null;

            try {
                // Prepare card for capture
                originalStyles = prepareCardForCapture(cardElement);

                // Add temporary class
                cardElement.classList.add('html2canvas-capture');

                // Convert to PNG
                const canvas = await html2canvas(cardElement, {
                    scale: 3,
                    backgroundColor: '#ffffff',
                    useCORS: true,
                    logging: false,
                    allowTaint: false,
                    imageTimeout: 0
                });

                // Download
                const link = document.createElement('a');
                link.download = `ID_${studentKey}.png`;
                link.href = canvas.toDataURL('image/png');
                link.click();

                console.log('File downloaded');

                // Update status
                const response = await fetch(`/admin/student-id-cards/${cardId}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ status: 'downloaded' })
                });

                const result = await response.json();

                if (result.success) {
                    alert('ID Card downloaded successfully!');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Downloaded but status update failed');
                }

            } catch (error) {
                console.error('Download error:', error);
                alert('Download failed: ' + error.message);
            } finally {
                // Restore original styles
                if (originalStyles) {
                    restoreCardStyles(cardElement, originalStyles);
                }
                cardElement.classList.remove('html2canvas-capture');
                buttonElement.innerHTML = originalText;
                buttonElement.disabled = false;
            }
        }

        // ==================== BULK DOWNLOAD ====================
        async function downloadBulkCards() {
            console.log('=== BULK DOWNLOAD STARTED ===');

            const selectedCheckboxes = document.querySelectorAll('.student-select:checked');

            if (selectedCheckboxes.length === 0) {
                alert('Please select at least one student');
                return;
            }

            if (!token) {
                alert('Security token not found. Please refresh the page.');
                return;
            }

            const cards = [];
            for (const checkbox of selectedCheckboxes) {
                const studentKey = checkbox.value;
                const card = document.querySelector(`.student-card[data-id="${studentKey}"]`);

                if (card) {
                    cards.push({
                        key: studentKey,
                        element: card.querySelector('.student-id-card'),
                        cardId: card.getAttribute('data-card-id')
                    });
                }
            }

            const bulkBtn = document.getElementById('bulkDownloadBtn');
            const originalText = bulkBtn.innerHTML;
            bulkBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating ZIP...';
            bulkBtn.disabled = true;

            // Store all original styles for restoration
            const allOriginalStyles = [];

            try {
                const images = [];

                for (let i = 0; i < cards.length; i++) {
                    bulkBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> Processing ${i + 1}/${cards.length}...`;

                    // Prepare card for capture
                    const originalStyles = prepareCardForCapture(cards[i].element);
                    allOriginalStyles.push({ element: cards[i].element, styles: originalStyles });
                    cards[i].element.classList.add('html2canvas-capture');

                    const canvas = await html2canvas(cards[i].element, {
                        scale: 3,
                        backgroundColor: '#ffffff',
                        useCORS: true,
                        logging: false,
                        allowTaint: false,
                        imageTimeout: 0
                    });

                    images.push({
                        key: cards[i].key,
                        data: canvas.toDataURL('image/png')
                    });
                }

                // Create ZIP
                const zip = new JSZip();
                images.forEach(img => {
                    const base64Data = img.data.split(',')[1];
                    zip.file(`ID_${img.key}.png`, base64Data, { base64: true });
                });

                const zipBlob = await zip.generateAsync({ type: 'blob' });
                const link = document.createElement('a');
                link.download = `ID_Cards_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.zip`;
                link.href = URL.createObjectURL(zipBlob);
                link.click();
                URL.revokeObjectURL(link.href);

                // Bulk status update
                const cardIds = cards.map(c => c.cardId);
                const response = await fetch('/admin/student-id-cards/bulk-status', {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_ids: cardIds,
                        status: 'downloaded'
                    })
                });

                const result = await response.json();

                if (result.success) {
                    alert(`${cards.length} ID Cards downloaded successfully!`);
                    setTimeout(() => location.reload(), 1500);
                } else {
                    alert('Downloaded but bulk status update failed');
                }

            } catch (error) {
                console.error('Bulk download error:', error);
                alert('Bulk download failed: ' + error.message);
            } finally {
                // Restore all original styles
                allOriginalStyles.forEach(({ element, styles }) => {
                    restoreCardStyles(element, styles);
                    element.classList.remove('html2canvas-capture');
                });
                bulkBtn.innerHTML = originalText;
                bulkBtn.disabled = false;
            }
        }

        // ==================== EVENT LISTENERS ====================
        const selectedStudents = new Set();
        const selectAllBtn = document.getElementById('selectAllBtn');
        const deselectAllBtn = document.getElementById('deselectAllBtn');
        const bulkDownloadBtn = document.getElementById('bulkDownloadBtn');

        function updateBulkActions() {
            const count = selectedStudents.size;
            const selectedCountSpan = document.getElementById('selectedCount');
            const downloadCountSpan = document.getElementById('downloadCount');

            if (selectedCountSpan) selectedCountSpan.textContent = count;
            if (downloadCountSpan) downloadCountSpan.textContent = count;
            if (bulkDownloadBtn) bulkDownloadBtn.disabled = count === 0;
        }

        // Single checkbox events
        // Update selection logic - only allow pending status
        document.querySelectorAll('.student-select').forEach(cb => {
            // Only enable if not disabled (pending status)
            if (!cb.disabled) {
                cb.addEventListener('change', function () {
                    const card = this.closest('.student-card');
                    if (this.checked) {
                        selectedStudents.add(this.value);
                        if (card) card.classList.add('selected');
                    } else {
                        selectedStudents.delete(this.value);
                        if (card) card.classList.remove('selected');
                    }
                    updateBulkActions();
                });
            }
        });

        // Select All button - only select pending ones
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.student-select:not(:disabled)').forEach(cb => {
                    cb.checked = true;
                    selectedStudents.add(cb.value);
                    const card = cb.closest('.student-card');
                    if (card) card.classList.add('selected');
                });
                updateBulkActions();
            });
        }

        // Select All
        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.student-select:not(:disabled)').forEach(cb => {
                    cb.checked = true;
                    selectedStudents.add(cb.value);
                    const card = cb.closest('.student-card');
                    if (card) card.classList.add('selected');
                });
                updateBulkActions();
            });
        }

        // Deselect All
        if (deselectAllBtn) {
            deselectAllBtn.addEventListener('click', () => {
                document.querySelectorAll('.student-select').forEach(cb => {
                    cb.checked = false;
                    const card = cb.closest('.student-card');
                    if (card) card.classList.remove('selected');
                });
                selectedStudents.clear();
                updateBulkActions();
            });
        }

        // Single download buttons
        document.querySelectorAll('.download-single-btn').forEach(btn => {
            const newBtn = btn.cloneNode(true);
            btn.parentNode.replaceChild(newBtn, btn);

            newBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                const cardId = this.getAttribute('data-card-id');
                const studentKey = this.getAttribute('data-student-key');
                downloadSingleCard(cardId, studentKey, this);
            });
        });

        // Bulk download button
        if (bulkDownloadBtn) {
            const newBulkBtn = bulkDownloadBtn.cloneNode(true);
            bulkDownloadBtn.parentNode.replaceChild(newBulkBtn, bulkDownloadBtn);

            newBulkBtn.addEventListener('click', function (e) {
                e.preventDefault();
                downloadBulkCards();
            });
        }

        updateBulkActions();
        console.log('Script initialized successfully!');

    });
</script>