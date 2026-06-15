@if($students->hasPages())
    <div class="row mt-4">
        <div class="col-12">
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center flex-wrap">
                    {{ $students->links() }}
                </ul>
            </nav>
        </div>
    </div>
@endif