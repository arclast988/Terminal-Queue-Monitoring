<label for="<?= esc($sortId, 'attr') ?>" class="<?= esc($labelClass ?? 'form-label-modern', 'attr') ?>">Order</label>
<select name="sort_order" id="<?= esc($sortId, 'attr') ?>" class="<?= esc($selectClass ?? 'input-modern', 'attr') ?>" data-no-autocomplete>
    <option value="desc" <?= record_sort_order($selectedOrder ?? $sort_order ?? 'desc') === 'desc' ? 'selected' : '' ?>>Newest first (Descending)</option>
    <option value="asc" <?= record_sort_order($selectedOrder ?? $sort_order ?? 'desc') === 'asc' ? 'selected' : '' ?>>Oldest first (Ascending)</option>
</select>
