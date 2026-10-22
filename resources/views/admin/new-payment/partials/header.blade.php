<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div>
        <h4 class="mb-1 fw-bold"><i class="bi bi-credit-card-2-front-fill me-2 text-primary"></i>Payment Management</h4>
        <p class="text-muted mb-0">Scan QR or type Student ID &rarr; choose classes &rarr; Pay &rarr; receipt prints</p>
    </div>
    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#shortcutsModal">
        <i class="bi bi-keyboard me-1"></i>Shortcuts <kbd class="ms-1">F1</kbd>
    </button>
</div>

{{-- Always-visible shortcut strip --}}
<div class="np-shortcut-strip mb-4">
    <span><kbd>F2</kbd> Student ID</span>
    <span><kbd>1</kbd>&ndash;<kbd>9</kbd> Select class</span>
    <span><kbd>A</kbd> All unpaid</span>
    <span><kbd>F8</kbd> Pay</span>
    <span><kbd>Enter</kbd> Confirm</span>
    <span><kbd>F9</kbd> Reprint</span>
    <span><kbd>Esc</kbd> Next student</span>
</div>

{{-- Shortcuts help --}}
<div class="modal fade" id="shortcutsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold"><i class="bi bi-keyboard me-2 text-primary"></i>Keyboard Shortcuts</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <table class="table table-sm align-middle mb-0 np-shortcut-table">
                    <tbody>
                        <tr><th colspan="2" class="text-muted small pt-2">Find student</th></tr>
                        <tr><td><kbd>F2</kbd> or <kbd>/</kbd></td><td>Go to Student ID box</td></tr>
                        <tr><td><kbd>Enter</kbd></td><td>Search the typed ID</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Clear and get ready for the next student</td></tr>
                        <tr><th colspan="2" class="text-muted small pt-3">Choose classes</th></tr>
                        <tr><td><kbd>1</kbd> &ndash; <kbd>9</kbd></td><td>Select / unselect class number 1&ndash;9</td></tr>
                        <tr><td><kbd>A</kbd></td><td>Select all unpaid classes (press again to clear)</td></tr>
                        <tr><td><kbd>F8</kbd> or <kbd>Ctrl</kbd>+<kbd>Enter</kbd></td><td>Pay the selected classes</td></tr>
                        <tr><th colspan="2" class="text-muted small pt-3">Payment window</th></tr>
                        <tr><td><kbd>Enter</kbd></td><td>Confirm payment &amp; print</td></tr>
                        <tr><td><kbd>Alt</kbd>+<kbd>1</kbd> &hellip; <kbd>6</kbd></td><td>Cash, Card, Bank, Online, Cheque, Other</td></tr>
                        <tr><td><kbd>Alt</kbd>+<kbd>M</kbd></td><td>Change payment month</td></tr>
                        <tr><td><kbd>Esc</kbd></td><td>Cancel</td></tr>
                        <tr><th colspan="2" class="text-muted small pt-3">After payment</th></tr>
                        <tr><td><kbd>Enter</kbd></td><td>Next student</td></tr>
                        <tr><td><kbd>F9</kbd></td><td>Print the last receipt again</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
