<div class="editor-body supplier-form">
    <section class="editor-section">
        <div class="editor-section-head">
            <div class="editor-section-title">Company Details</div>
            <div class="editor-section-copy">Add the supplier and your main point of contact.</div>
        </div>
        <div class="form-group">
            <label>Company Name *</label>
            <input type="text" name="name" value="<?php echo e(old('name', $s->name ?? '')); ?>" placeholder="Supplier company name" required autofocus>
        </div>
        <div class="grid-2">
            <div class="form-group">
                <label>Contact Person</label>
                <input type="text" name="contact" value="<?php echo e(old('contact', $s->contact ?? '')); ?>" placeholder="Full name">
            </div>
            <div class="form-group">
                <label>Phone</label>
                <input type="tel" name="phone" value="<?php echo e(old('phone', $s->phone ?? '')); ?>" placeholder="09xxxxxxxxx" autocomplete="tel">
            </div>
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?php echo e(old('email', $s->email ?? '')); ?>" placeholder="email@example.com" autocomplete="email">
        </div>
        <div class="form-group">
            <label>Address</label>
            <textarea name="address" rows="2" placeholder="Full address"><?php echo e(old('address', $s->address ?? '')); ?></textarea>
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
                    <option value="active" <?php echo e(old('status', $s->status ?? 'active') == 'active' ? 'selected' : ''); ?>>Active</option>
                    <option value="inactive" <?php echo e(old('status', $s->status ?? 'active') == 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                </select>
            </div>
            <div class="form-group">
                <label>Payment Method *</label>
                <select name="payment_method" class="supplier-payment-method" required>
                    <option value="cash" <?php echo e(old('payment_method', $s->payment_method ?? 'cash') == 'cash' ? 'selected' : ''); ?>>Cash</option>
                    <option value="check" <?php echo e(old('payment_method', $s->payment_method ?? 'cash') == 'check' ? 'selected' : ''); ?>>Check</option>
                    <option value="credit_account" <?php echo e(old('payment_method', $s->payment_method ?? 'cash') == 'credit_account' ? 'selected' : ''); ?>>Credit Sale</option>
                    <option value="gcash" <?php echo e(old('payment_method', $s->payment_method ?? 'cash') == 'gcash' ? 'selected' : ''); ?>>GCash</option>
                </select>
            </div>
        </div>
        <div class="supplier-check-note editor-notice" style="display:<?php echo e(old('payment_method', $s->payment_method ?? 'cash') == 'check' ? '' : 'none'); ?>;">
            <strong>Check payment:</strong> Record the check number and bank in the purchase order notes.
        </div>
    </section>
</div>

<?php if (! $__env->hasRenderedOnce('766e67e6-888e-4fdd-ba46-b9eb6941a50c')): $__env->markAsRenderedOnce('766e67e6-888e-4fdd-ba46-b9eb6941a50c'); ?>
<?php echo app('Illuminate\Foundation\Vite')('resources/js/views/suppliers-_form-1.js'); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views/suppliers/_form.blade.php ENDPATH**/ ?>