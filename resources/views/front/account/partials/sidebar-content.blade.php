<div class="sidebar-group mb-2">
    <ul class="list-group">
        <li class="list-group-item d-flex justify-content-between align-items-center">
            <a href="{{ url('/') }}" class="text-decoration-none d-flex w-100 align-items-center gap-3 text-body">
                <i class="bi bi-house-door fs-5 opacity-75"></i> <span class="flex-grow-1 text-start">Página Inicial</span>
            </a>
        </li>
    </ul>
</div>

@if ($accountMenu->isNotEmpty())
    @foreach($accountMenu as $group => $menu)
        <div class="sidebar-group">
            @if ($group !== 'Navegação' && $group !== '')
                <h6 class="text-uppercase text-muted fw-bold mb-2 mt-4 ps-3" style="font-size: 0.75rem; letter-spacing: 0.5px;">
                    {{ $group }}
                </h6>
            @endif
            
            @if (!empty($menu))
                <ul class="list-group">
                    @foreach($menu as $key => $item)
                        @php
                            $isActive = $item['isActive'] ?? false;
                            $activeClass = $isActive ? 'active shadow-sm' : '';
                            $activeAttr = $isActive ? ' aria-current="true"' : '';
                            $isCrossLink = $item['isCrossLink'] ?? false;
                            
                            $linkClass = 'text-decoration-none d-flex w-100 align-items-center gap-3 ';
                            if ($isCrossLink) {
                                $liClass = 'list-group-item p-0 mb-3 border-0 bg-transparent';
                                $linkClass .= 'btn btn-primary text-white fw-bold py-3 px-4 rounded-4 justify-content-center text-center shadow-sm cross-link-btn';
                            } else {
                                $liClass = "list-group-item d-flex justify-content-between align-items-center {$activeClass}";
                                $linkClass .= $isActive ? 'text-white fw-semibold' : 'text-body';
                            }
                        @endphp
                        
                        @if($isCrossLink)
                            <div class="mb-3 mt-1">
                                <a href="{{ $item['url'] }}" class="{{ $linkClass }}">
                                    <i class="{{ $item['icon'] }} fs-5"></i> <span>{{ $item['name'] }}</span>
                                </a>
                            </div>
                        @else
                            <li class="{{ $liClass }}"{!! $activeAttr !!}>
                                <a href="{{ $item['url'] }}" class="{{ $linkClass }}">
                                    <i class="{{ $item['icon'] }} fs-5 opacity-75"></i> <span class="flex-grow-1 text-start">{{ $item['name'] }}</span>
                                </a>
                                @if (!empty($item['countVar']))
                                    <span class="badge rounded-pill bg-white text-primary border border-primary-subtle{{ $item['cssClass'] ?? '' }}">
                                        {{ \App\Helpers\Common\Num::short($item['countVar']) }}
                                    </span>
                                @endif
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endif
        </div>
	@endforeach
@endif
