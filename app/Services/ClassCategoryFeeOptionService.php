<?php

namespace App\Services;

use App\Models\ClassCategoryFee;
use App\Models\ClassCategoryFeeOption;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ClassCategoryFeeOptionService
{
    /**
     * Get all fee options for a class category fee.
     *
     * @param int $classCategoryFeeId
     * @param bool $activeOnly
     * @return Collection
     */
    public function getOptionsByCategoryFee($classCategoryFeeId, $activeOnly = false)
    {
        $query = ClassCategoryFeeOption::where(
            'class_category_fee_id',
            $classCategoryFeeId
        );

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query
            ->orderByDesc('is_default')
            ->orderBy('fee', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    /**
     * Get a single fee option.
     *
     * @param int $id
     * @return ClassCategoryFeeOption
     */
    public function find($id)
    {
        return ClassCategoryFeeOption::with([
            'classCategoryFee.category',
            'classCategoryFee.studentClass',
        ])->findOrFail($id);
    }

    /**
     * Get active fee options for a category fee.
     *
     * @param int $classCategoryFeeId
     * @return Collection
     */
    public function getActiveOptions($classCategoryFeeId)
    {
        return $this->getOptionsByCategoryFee(
            $classCategoryFeeId,
            true
        );
    }

    /**
     * Create a new fee option.
     *
     * @param array $data
     * @return ClassCategoryFeeOption
     */
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            $classCategoryFee = $this->getValidCategoryFee(
                $data['class_category_fee_id']
            );

            /*
            |--------------------------------------------------------------------------
            | If this is the first option for the category fee,
            | automatically make it default.
            |--------------------------------------------------------------------------
            */

            $hasOptions = ClassCategoryFeeOption::where(
                'class_category_fee_id',
                $classCategoryFee->id
            )->exists();

            if (!$hasOptions) {
                $data['is_default'] = true;
            }

            /*
            |--------------------------------------------------------------------------
            | Only one default option
            |--------------------------------------------------------------------------
            */

            if (!empty($data['is_default'])) {
                $this->removeDefaultFromOthers(
                    $classCategoryFee->id
                );
            }

            return ClassCategoryFeeOption::create($data);
        });
    }

    /**
     * Update an existing fee option.
     *
     * @param int $id
     * @param array $data
     * @return ClassCategoryFeeOption
     */
    public function update($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {

            $option = ClassCategoryFeeOption::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Validate category fee if it is being changed.
            |--------------------------------------------------------------------------
            */

            if (isset($data['class_category_fee_id'])) {

                $this->getValidCategoryFee(
                    $data['class_category_fee_id']
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent changing the category fee of an option
            | that is already used by enrollments.
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['class_category_fee_id']) &&
                (int) $data['class_category_fee_id'] !==
                (int) $option->class_category_fee_id
            ) {

                if ($option->enrollments()->exists()) {
                    throw ValidationException::withMessages([
                        'class_category_fee_id' =>
                            'This fee option is already used by an enrollment and cannot be moved to another category.'
                    ]);
                }
            }

            $categoryFeeId = isset($data['class_category_fee_id'])
                ? $data['class_category_fee_id']
                : $option->class_category_fee_id;

            /*
            |--------------------------------------------------------------------------
            | Default handling
            |--------------------------------------------------------------------------
            */

            if (!empty($data['is_default'])) {
                $this->removeDefaultFromOthers(
                    $categoryFeeId,
                    $option->id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | If the current default is being disabled,
            | automatically select another active option as default.
            |--------------------------------------------------------------------------
            */

            if (
                isset($data['is_active']) &&
                !$data['is_active'] &&
                $option->is_default
            ) {

                $replacement = ClassCategoryFeeOption::where(
                    'class_category_fee_id',
                    $categoryFeeId
                )
                    ->where('id', '!=', $option->id)
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->first();

                if ($replacement) {
                    $replacement->update([
                        'is_default' => true,
                    ]);
                }

                $data['is_default'] = false;
            }

            $option->update($data);

            return $option->fresh([
                'classCategoryFee.category',
                'classCategoryFee.studentClass',
            ]);
        });
    }

    /**
     * Delete a fee option.
     *
     * Soft delete is used because the model uses SoftDeletes.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return DB::transaction(function () use ($id) {

            $option = ClassCategoryFeeOption::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Do not delete an option which is already used by enrollments.
            |
            | This protects historical enrollment/payment data.
            |--------------------------------------------------------------------------
            */

            if ($option->enrollments()->exists()) {
                throw ValidationException::withMessages([
                    'fee_option' =>
                        'This fee option is already used by students and cannot be deleted. You can deactivate it instead.'
                ]);
            }

            $wasDefault = $option->is_default;
            $categoryFeeId = $option->class_category_fee_id;

            $deleted = $option->delete();

            /*
            |--------------------------------------------------------------------------
            | If the deleted option was default,
            | select another active option as default.
            |--------------------------------------------------------------------------
            */

            if ($deleted && $wasDefault) {

                $replacement = ClassCategoryFeeOption::where(
                    'class_category_fee_id',
                    $categoryFeeId
                )
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->first();

                if ($replacement) {
                    $replacement->update([
                        'is_default' => true,
                    ]);
                }
            }

            return $deleted;
        });
    }

    /**
     * Restore a deleted fee option.
     *
     * @param int $id
     * @return ClassCategoryFeeOption
     */
    public function restore($id)
    {
        return DB::transaction(function () use ($id) {

            $option = ClassCategoryFeeOption::withTrashed()
                ->findOrFail($id);

            $option->restore();

            return $option->fresh([
                'classCategoryFee.category',
                'classCategoryFee.studentClass',
            ]);
        });
    }

    /**
     * Set a fee option as the default option.
     *
     * @param int $id
     * @return ClassCategoryFeeOption
     */
    public function setDefault($id)
    {
        return DB::transaction(function () use ($id) {

            $option = ClassCategoryFeeOption::findOrFail($id);

            /*
            |--------------------------------------------------------------------------
            | Default option must be active.
            |--------------------------------------------------------------------------
            */

            if (!$option->is_active) {
                throw ValidationException::withMessages([
                    'fee_option' =>
                        'An inactive fee option cannot be set as default.'
                ]);
            }

            $this->removeDefaultFromOthers(
                $option->class_category_fee_id,
                $option->id
            );

            $option->update([
                'is_default' => true,
            ]);

            return $option->fresh([
                'classCategoryFee.category',
                'classCategoryFee.studentClass',
            ]);
        });
    }

    /**
     * Get the default fee option.
     *
     * @param int $classCategoryFeeId
     * @return ClassCategoryFeeOption|null
     */
    public function getDefaultOption($classCategoryFeeId)
    {
        return ClassCategoryFeeOption::where(
            'class_category_fee_id',
            $classCategoryFeeId
        )
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get default option or first active option.
     *
     * Useful when creating enrollments.
     *
     * @param int $classCategoryFeeId
     * @return ClassCategoryFeeOption|null
     */
    public function getDefaultOrFirstActiveOption($classCategoryFeeId)
    {
        $default = $this->getDefaultOption(
            $classCategoryFeeId
        );

        if ($default) {
            return $default;
        }

        return ClassCategoryFeeOption::where(
            'class_category_fee_id',
            $classCategoryFeeId
        )
            ->where('is_active', true)
            ->orderBy('id')
            ->first();
    }

    /**
     * Validate class category fee.
     *
     * @param int $classCategoryFeeId
     * @return ClassCategoryFee
     */
    protected function getValidCategoryFee($classCategoryFeeId)
    {
        return ClassCategoryFee::where(
            'id',
            $classCategoryFeeId
        )
            ->where('is_active', true)
            ->firstOrFail();
    }

    /**
     * Remove default flag from other options.
     *
     * @param int $classCategoryFeeId
     * @param int|null $exceptId
     * @return void
     */
    protected function removeDefaultFromOthers(
        $classCategoryFeeId,
        $exceptId = null
    ) {
        $query = ClassCategoryFeeOption::where(
            'class_category_fee_id',
            $classCategoryFeeId
        );

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $query->update([
            'is_default' => false,
        ]);
    }
}