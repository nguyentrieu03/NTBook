@if ($attributes->isEmpty())
    <div class="attr-empty">
        Chưa có thuộc tính nào trong thư viện.<br>
        <span class="muted">Bấm <strong>Thêm thuộc tính</strong> để bắt đầu.</span>
    </div>
@else
    <div class="attr-table-wrap">
        <table class="data-table attr-table">
            <thead>
                <tr>
                    <th>Tên</th>
                    <th>Code</th>
                    <th class="center">Số giá trị</th>
                    <th>Xem trước</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="attr-table-body">
                @foreach ($attributes as $attribute)
                    @php
                        $values = $attribute->attributeValues->pluck('value');
                        $preview = $values->take(3);
                        $rest = max(0, $values->count() - 3);
                        $search = \Illuminate\Support\Str::lower($attribute->name.' '.$attribute->code);
                    @endphp
                    <tr data-search="{{ $search }}"
                        data-attr-id="{{ $attribute->id }}"
                        data-attr-name="{{ $attribute->name }}"
                        data-attr-code="{{ $attribute->code }}"
                        data-attr-values='@json($values->values())'>
                        <td><div class="attr-td-name">{{ $attribute->name }}</div></td>
                        <td><code class="attr-td-code">{{ $attribute->code }}</code></td>
                        <td class="attr-td-count center">
                            <span class="attr-count-badge">{{ $values->count() }}</span>
                        </td>
                        <td class="attr-td-preview">
                            @if ($values->isEmpty())
                                <span class="attr-preview-empty">Chưa có giá trị</span>
                            @else
                                <div class="attr-preview-chips">
                                    @foreach ($preview as $value)
                                        <span class="attr-val-chip">{{ $value }}</span>
                                    @endforeach
                                    @if ($rest > 0)
                                        <span class="attr-more-chip" title="{{ $values->slice(3)->implode(', ') }}">+{{ $rest }}</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="attr-td-actions">
                            <div class="attr-row-actions">
                                <button type="button" class="btn--icon js-attr-edit" title="Sửa">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('admin.attributes.destroy', $attribute, absolute: false) }}" class="js-attr-delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn--icon attr-del" title="Xoá" data-name="{{ $attribute->name }}">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><path d="M3 6h18M8 6V4h8v2M19 6l-1 14H6L5 6"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr id="attr-no-results" class="is-hidden">
                    <td colspan="5">
                        <div class="attr-empty" style="margin:0;border:none;background:transparent;padding:20px 0">
                            Không tìm thấy thuộc tính phù hợp.
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
@endif
