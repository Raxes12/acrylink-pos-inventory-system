<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Stock Report</title>
    <style>
        @page { margin: 20px; }
        body { font-family: DejaVu Sans, sans-serif; color: #172033; font-size: 8px; }
        h1 { margin: 0; font-size: 17px; color: #9d1118; }
        h2 { margin: 4px 0 2px; font-size: 13px; }
        .meta { color: #526074; margin-bottom: 12px; }
        .summary { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .summary td { padding: 6px; border: 1px solid #ccd4df; }
        .summary strong { color: #526074; text-transform: uppercase; font-size: 7px; }
        table.data { width: 100%; border-collapse: collapse; table-layout: fixed; }
        .data th { background: #9d1118; color: white; padding: 5px 3px; border: 1px solid #780c12; }
        .data td { padding: 4px 3px; border: 1px solid #cfd6df; overflow-wrap: break-word; }
        .data tr:nth-child(even) td { background: #f3f6f8; }
        .number { text-align: right; }
        .product { width: 15%; }
        .empty { text-align: center; padding: 20px; color: #6b7280; }
    </style>
</head>
<body>
    <?php
        $totalStock = $variants->sum(fn ($variant) => (int) ($variant->inventoryStock?->quantity_on_hand ?? 0));
        $totalValue = $variants->sum(fn ($variant) => (float) $variant->cost_price * (int) ($variant->inventoryStock?->quantity_on_hand ?? 0));
    ?>
    <h1><?php echo e($companyName); ?></h1>
    <h2>Inventory Stock Report</h2>
    <div class="meta">Date as of <?php echo e(now()->format('F j, Y')); ?> | <?php echo e($filterLabel); ?> | Generated <?php echo e(now()->format('g:i A')); ?></div>
    <table class="summary"><tr>
        <td><strong>Variants:</strong> <?php echo e(number_format($variants->count())); ?></td>
        <td><strong>Total On Hand:</strong> <?php echo e(number_format($totalStock)); ?></td>
        <td><strong>Inventory Value:</strong> PHP <?php echo e(number_format($totalValue, 2)); ?></td>
    </tr></table>
    <table class="data">
        <thead><tr><th class="product">Product</th><th>Part Number</th><th>Variant</th><th>Brand</th><th>UOM</th><th>Supplier</th><th class="number">Cost</th><th class="number">Total Cost</th><th class="number">Selling</th><th class="number">On Hand</th><th class="number">Min.</th><th>Status</th></tr></thead>
        <tbody>
        <?php $__empty_1 = true; $__currentLoopData = $variants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $variant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php $quantity = (int) ($variant->inventoryStock?->quantity_on_hand ?? 0); ?>
            <tr>
                <td><?php echo e($variant->product?->name ?? 'Deleted product'); ?></td>
                <td><?php echo e($variant->part_number ?: '-'); ?></td>
                <td><?php echo e($variant->variant_code); ?></td>
                <td><?php echo e($variant->brand?->name ?? '-'); ?></td>
                <td><?php echo e($variant->unit?->code ?? '-'); ?></td>
                <td><?php echo e($variant->supplier?->name ?? '-'); ?></td>
                <td class="number"><?php echo e(number_format((float) $variant->cost_price, 2)); ?></td>
                <td class="number"><?php echo e(number_format((float) $variant->cost_price * $quantity, 2)); ?></td>
                <td class="number"><?php echo e(number_format((float) $variant->selling_price, 2)); ?></td>
                <td class="number"><?php echo e(number_format($quantity)); ?></td>
                <td class="number"><?php echo e(number_format((int) ($variant->inventoryStock?->minimum_stock ?? 0))); ?></td>
                <td><?php echo e($quantity <= 0 ? 'Out of Stock' : ($quantity <= (int) ($variant->inventoryStock?->critical_stock ?? 0) ? 'Critical' : ($quantity <= (int) ($variant->inventoryStock?->minimum_stock ?? 0) ? 'Low Stock' : 'Normal'))); ?></td>
            </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="12" class="empty">No inventory records match the selected filters.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\acrylink_newbie\resources\views\reports\exports\inventory-pdf.blade.php ENDPATH**/ ?>