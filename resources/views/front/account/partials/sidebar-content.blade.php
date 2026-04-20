@if ($accountMenu->isNotEmpty())
    @foreach($accountMenu as $group => $menu)
        @php
            $collapseId = str($group)->slug();
        @endphp
        <div class="sidebar-group">
            <h5 class="border-0 fw-bold clearfix d-flex justify-content-between align-items-center">
                {{ $group }}
                <a href="#{{ $collapseId }}-content"
                   data-bs-toggle="collapse"
                   aria-expanded="false"
                   aria-controls="{{ $collapseId }}-content"
                   class="{{ $linkClass }}"
                >
                    <i class="fa-solid fa-angle-down"></i>
                </a>
            </h5>
            @if (!empty($menu))
                <div class="collapse show" id="{{ $collapseId }}-content">
                    <ul class="list-group">
                        @foreach($menu as $key => $item)
                            @php
                                $activeClass = $item['isActive'] ? 'active' : '';
                                $activeAttr = $item['isActive'] ? ' aria-current="true"' : '';
                                $activeLinkClass = $item['isActive'] ? 'text-white' : '';
                            @endphp
                            <li class="list-group-item d-flex justify-content-between align-items-center {{ $activeClass }}"{!! $activeAttr !!}>
                                <a href="{{ $item['url'] }}" class="{{ $activeLinkClass }} text-decoration-none">
                                    <i class="{{ $item['icon'] }} opacity-75"></i> {{ $item['name'] }}
                                </a>
                                @if (!empty($item['countVar']))
                                    <span class="badge rounded-pill text-bg-secondary{{ $item['cssClass'] ?? '' }}">
                                        {{ \App\Helpers\Common\Num::short($item['countVar']) }}
                                    </span>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @endforeach
@endif
