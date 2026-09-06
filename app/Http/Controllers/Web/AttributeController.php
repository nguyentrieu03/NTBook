<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttributeController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:60', 'regex:/^[a-z0-9_-]+$/i', 'unique:attributes,code'],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:120'],
        ]);

        $attribute = Attribute::create([
            'name' => $validated['name'],
            'code' => Str::lower($validated['code']),
            'is_active' => true,
        ]);

        $this->syncValues($attribute, $validated['values'] ?? []);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã tạo thuộc tính "'.$attribute->name.'".');
    }

    public function update(Request $request, Attribute $attribute): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => [
                'required', 'string', 'max:60', 'regex:/^[a-z0-9_-]+$/i',
                Rule::unique('attributes', 'code')->ignore($attribute->id),
            ],
            'values' => ['nullable', 'array'],
            'values.*' => ['nullable', 'string', 'max:120'],
        ]);

        $attribute->update([
            'name' => $validated['name'],
            'code' => Str::lower($validated['code']),
        ]);

        $this->syncValues($attribute, $validated['values'] ?? []);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã cập nhật thuộc tính "'.$attribute->name.'".');
    }

    public function destroy(Attribute $attribute): RedirectResponse
    {
        $name = $attribute->name;
        $attribute->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Đã xóa thuộc tính "'.$name.'".');
    }

    /**
     * @param  array<int, string>|string  $raw  Mảng giá trị (từ Select2) hoặc chuỗi phân tách dòng/phẩy.
     */
    private function syncValues(Attribute $attribute, array|string $raw): void
    {
        $items = is_array($raw)
            ? $raw
            : (preg_split('/\r\n|\r|\n|,/', $raw) ?: []);

        $lines = collect($items)
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->unique(fn ($v) => $this->normalizeAttributeValue($v))
            ->values();

        $keepIds = [];

        foreach ($lines as $value) {
            $normalized = $this->normalizeAttributeValue($value);
            $existing = $attribute->attributeValues()
                ->where('normalized_value', $normalized)
                ->first();

            if ($existing) {
                $existing->update(['value' => $value, 'is_active' => true]);
                $keepIds[] = $existing->id;
                continue;
            }

            $created = AttributeValue::create([
                'attribute_id' => $attribute->id,
                'value' => $value,
                'normalized_value' => $normalized,
                'is_active' => true,
            ]);
            $keepIds[] = $created->id;
        }

        $attribute->attributeValues()
            ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds))
            ->update(['is_active' => false]);
    }

    /** Bỏ dấu tiếng Việt + lowercase — dùng cho dedup (VD: "Vàng" → "vang"). */
    private function normalizeAttributeValue(string $value): string
    {
        return Str::lower(Str::ascii($value));
    }
}
