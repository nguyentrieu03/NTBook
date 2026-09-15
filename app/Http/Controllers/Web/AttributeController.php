<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Attribute\CreateAttributeRequest;
use App\Http\Requests\Attribute\UpdateAttributeRequest;
use App\Domains\Attribute\Contracts\AttributeServiceInterface;
use App\Domains\Attribute\DTOs\CreateAttributeDTO;
use App\Domains\Attribute\DTOs\UpdateAttributeDTO;

class AttributeController extends Controller
{
    public function __construct(
        private readonly AttributeServiceInterface $attributeService
    ) {}

    public function store(CreateAttributeRequest $request): RedirectResponse
    {
        $attribute = $this->attributeService->create(
            CreateAttributeDTO::fromRequest($request->validated())
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã tạo thuộc tính "'.$attribute->name.'".');
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        $attribute = $this->attributeService->update(
            $attribute->id,
            UpdateAttributeDTO::fromRequest($request->validated())
        );

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã cập nhật thuộc tính "'.$attribute->name.'".');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $name = $attribute->name;
        $this->attributeService->delete($attribute->id);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã xóa thuộc tính "'.$name.'".');
    }
}
