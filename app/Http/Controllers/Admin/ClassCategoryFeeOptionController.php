<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassCategoryFee;
use App\Services\ClassCategoryFeeOptionService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ClassCategoryFeeOptionController extends Controller
{
    protected ClassCategoryFeeOptionService $feeOptionService;

    /**
     * Create a new controller instance.
     *
     * @param ClassCategoryFeeOptionService $feeOptionService
     */
    public function __construct(
        ClassCategoryFeeOptionService $feeOptionService
    ) {
        $this->feeOptionService = $feeOptionService;
    }

    /**
     * Display fee options for a category fee.
     *
     * Example:
     * /admin/class-category-fee-options?class_category_fee_id=10
     */
    public function index(Request $request)
    {
        $classCategoryFeeId = $request->get('class_category_fee_id');

        if (!$classCategoryFeeId) {
            return redirect()
                ->route('admin.class-category-fees.index')
                ->with('error', 'Class Category Fee is required.');
        }

        $classCategoryFee = ClassCategoryFee::with([
            'category',
            'studentClass',
        ])->findOrFail($classCategoryFeeId);

        $options = $this->feeOptionService->getOptionsByCategoryFee(
            $classCategoryFeeId,
            false
        );

        return view('admin.class-category-fee-options.index', [
            'classCategoryFee' => $classCategoryFee,
            'options' => $options,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(Request $request)
    {
        $classCategoryFeeId = $request->get(
            'class_category_fee_id'
        );

        if (!$classCategoryFeeId) {
            return redirect()
                ->route('admin.class-category-fee-options.index')
                ->with('error', 'Please select a class category first.');
        }

        $classCategoryFee = ClassCategoryFee::with([
            'category',
            'studentClass',
        ])->findOrFail($classCategoryFeeId);

        return view(
            'admin.class-category-fee-options.create',
            compact('classCategoryFee')
        );
    }

    /**
     * Store a new fee option.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_category_fee_id' => [
                'required',
                'integer',
                'exists:class_category_fees,id',
            ],

            'label' => [
                'required',
                'string',
                'max:150',
            ],

            'fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Checkbox handling
        |--------------------------------------------------------------------------
        */

        $validated['is_default'] = $request->boolean(
            'is_default'
        );

        $validated['is_active'] = $request->boolean(
            'is_active'
        );

        try {

            $option = $this->feeOptionService->create(
                $validated
            );

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $option->class_category_fee_id,
                    ]
                )
                ->with(
                    'success',
                    'Fee option created successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Show edit form.
     */
    public function edit($id)
    {
        $option = $this->feeOptionService->find($id);

        $classCategoryFee = $option->classCategoryFee;

        return view(
            'admin.class-category-fee-options.edit',
            compact(
                'option',
                'classCategoryFee'
            )
        );
    }

    /**
     * Update fee option.
     */
    public function update(
        Request $request,
        $id
    ) {
        $validated = $request->validate([
            'class_category_fee_id' => [
                'required',
                'integer',
                'exists:class_category_fees,id',
            ],

            'label' => [
                'required',
                'string',
                'max:150',
            ],

            'fee' => [
                'required',
                'numeric',
                'min:0',
            ],

            'is_default' => [
                'nullable',
                'boolean',
            ],

            'is_active' => [
                'nullable',
                'boolean',
            ],

            'note' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Checkbox handling
        |--------------------------------------------------------------------------
        */

        $validated['is_default'] = $request->boolean(
            'is_default'
        );

        $validated['is_active'] = $request->boolean(
            'is_active'
        );

        try {

            $option = $this->feeOptionService->update(
                $id,
                $validated
            );

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $option->class_category_fee_id,
                    ]
                )
                ->with(
                    'success',
                    'Fee option updated successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }

    /**
     * Delete fee option.
     */
    public function destroy($id)
    {
        try {

            $option = $this->feeOptionService->find($id);

            $classCategoryFeeId =
                $option->class_category_fee_id;

            $this->feeOptionService->delete($id);

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $classCategoryFeeId,
                    ]
                )
                ->with(
                    'success',
                    'Fee option deleted successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Set fee option as default.
     */
    public function setDefault($id)
    {
        try {

            $option = $this->feeOptionService
                ->setDefault($id);

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $option->class_category_fee_id,
                    ]
                )
                ->with(
                    'success',
                    'Default fee option updated successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Activate fee option.
     */
    public function activate($id)
    {
        try {

            $option = $this->feeOptionService->update(
                $id,
                [
                    'is_active' => true,
                ]
            );

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $option->class_category_fee_id,
                    ]
                )
                ->with(
                    'success',
                    'Fee option activated successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Deactivate fee option.
     */
    public function deactivate($id)
    {
        try {

            $option = $this->feeOptionService->find($id);

            /*
            |--------------------------------------------------------------------------
            | If this is the default option, service will
            | automatically handle the replacement default.
            |--------------------------------------------------------------------------
            */

            $updatedOption = $this->feeOptionService->update(
                $id,
                [
                    'is_active' => false,
                    'is_default' => false,
                ]
            );

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $option->class_category_fee_id,
                    ]
                )
                ->with(
                    'success',
                    'Fee option deactivated successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /**
     * Restore a deleted fee option.
     */
    public function restore($id)
    {
        try {

            $option = $this->feeOptionService->restore(
                $id
            );

            return redirect()
                ->route(
                    'admin.class-category-fee-options.index',
                    [
                        'class_category_fee_id' =>
                        $option->class_category_fee_id,
                    ]
                )
                ->with(
                    'success',
                    'Fee option restored successfully.'
                );
        } catch (ValidationException $e) {

            return back()
                ->withErrors($e->errors())
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    public function byCategoryFee($classCategoryFeeId)
    {
        $options = $this->feeOptionService
            ->getActiveOptions($classCategoryFeeId);

        return response()->json(
            $options->map(function ($option) {
                return [
                    'id' => $option->id,
                    'label' => $option->label,
                    'fee' => (float) $option->fee,
                    'is_default' => (bool) $option->is_default,
                    'note' => $option->note,
                ];
            })->values()
        );
    }
}
