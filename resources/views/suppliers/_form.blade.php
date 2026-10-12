<div class="editor-body supplier-form">
    <section class="editor-section">
        <div class="editor-section-head">
            <div class="editor-section-title">Company Details</div>
            <div class="editor-section-copy">Add the supplier and your main point of contact.</div>
        </div>
        <div class="form-group">
            <label>Company Name *</label>
            <input type="text" name="name" value="{{ old('name', $s->name ?? '') }}" placeholder="Supplier company name" required autofocus>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact" value="{{ old('contact', $s->contact ?? '') }}" placeholder="Full name">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="{{ old('phone', $s->phone ?? '') }}" placeholder="09xxxxxxxxx" autocomplete="tel">
            </div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email', $s->email ?? '') }}" placeholder="email@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="2" placeholder="Full address">{{ old('address', $s->address ?? '') }}</textarea>
        </div>
    </section>

    <section class="editor-section">
        <div class="editor-section-head">
            <div class="editor-section-title">Account Preferences</div>
            <div class="editor-section-copy">Choose availability and the default payment method.</div>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Status *</label>
                <select name="status" required>
                    <option value="active" {{ old('status', $s->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('status', $s->status ?? 'active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment_method" class="supplier-payment-method" required>
                    <option value="cash" {{ old('payment_method', $s->payment_method ?? 'cash') == 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="check" {{ old('payment_method', $s->payment_method ?? 'cash') == 'check' ? 'selected' : '' }}>Check</option>
                    <option value="credit_account" {{ old('payment_method', $s->payment_method ?? 'cash') == 'credit_account' ? 'selected' : '' }}>Credit Sale</option>
                    <option value="gcash" {{ old('payment_method', $s->payment_method ?? 'cash') == 'gcash' ? 'selected' : '' }}>GCash</option>
                </select>
            </div>
        </div>
        <div class="supplier-check-note editor-notice" style="display:{{ old('payment_method', $s->payment_method ?? 'cash') == 'check' ? '' : 'none' }};">
            <strong>Check payment:</strong> Record the check number and bank in the purchase order notes.
        </div>
    </section>
</div>

@once
@vite('resources/js/views/suppliers-_form-1.js')
@endonce
