<nav class="inventory-local-nav" aria-label="Inventory sections">
    <a href="{{ route('inventory.index') }}" class="{{ request()->routeIs('inventory.index', 'inventory.create', 'inventory.edit', 'inventory.show') ? 'is-active' : '' }}" @if(request()->routeIs('inventory.index', 'inventory.create', 'inventory.edit', 'inventory.show')) aria-current="page" @endif>
        <span class="inventory-local-nav-icon"><x-icon name="products" /></span>
        <span class="inventory-local-nav-copy"><strong>Products</strong><small>Catalog &amp; pricing</small></span>
    </a>
    <a href="{{ route('inventory.stock-overview') }}" class="{{ request()->routeIs('inventory.stock-overview') ? 'is-active' : '' }}" @if(request()->routeIs('inventory.stock-overview')) aria-current="page" @endif>
        <span class="inventory-local-nav-icon"><x-icon name="stock" /></span>
        <span class="inventory-local-nav-copy"><strong>Stock Overview</strong><small>Levels &amp; alerts</small></span>
    </a>
    <a href="{{ route('inventory.movements') }}" class="{{ request()->routeIs('inventory.movements') ? 'is-active' : '' }}" @if(request()->routeIs('inventory.movements')) aria-current="page" @endif>
        <span class="inventory-local-nav-icon"><x-icon name="transactions" /></span>
        <span class="inventory-local-nav-copy"><strong>Movements</strong><small>Stock history</small></span>
    </a>
    @if(auth()->user()->canEdit())
    <a href="{{ route('stock-receipts.index') }}" class="{{ request()->routeIs('stock-receipts.*') ? 'is-active' : '' }}" @if(request()->routeIs('stock-receipts.*')) aria-current="page" @endif>
        <span class="inventory-local-nav-icon"><x-icon name="receipts" /></span>
        <span class="inventory-local-nav-copy"><strong>Stock Receipts</strong><small>Incoming inventory</small></span>
    </a>
    @endif
</nav>
