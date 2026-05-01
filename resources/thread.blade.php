@php
	$thread ??= [];
	$isLastThread ??= false;
	
	$userName = data_get($thread, 'p_creator.name');
	$avatarUrl = url(data_get($thread, 'p_creator.photo_url', ''));
	$userIsOnline = isUserOnline(data_get($thread, 'p_creator')) ? 'online text-success' : 'offline text-secondary';
	
	$msgUri = urlGen()->getAccountBasePath() . '/messages/' . data_get($thread, 'id');
	$msgSubject = data_get($thread, 'subject');
	$msgBody = str(data_get($thread, 'latest_message.body') ?? '')->limit(50);
	$msgCreatedAt = data_get($thread, 'created_at_formatted', data_get($thread, 'created_at'));
	$isImportant = data_get($thread, 'p_is_important');
	$isUnread = data_get($thread, 'p_is_unread');
	
	$activeClass = (request()->segment(3) == data_get($thread, 'id')) ? ' active' : '';
	$unreadClass = $isUnread ? ' fw-bold' : '';
@endphp
<a href="{{ url($msgUri) }}" class="messenger-thread-item d-flex align-items-center{{ $activeClass . $unreadClass }}">
    <div class="position-relative">
        <img src="{{ $avatarUrl }}" class="thread-avatar" alt="{{ $userName }}">
        @if (isUserOnline(data_get($thread, 'p_creator')))
            <span class="position-absolute bottom-0 end-0 p-1 bg-success border border-light rounded-circle" style="width: 12px; height: 12px;"></span>
        @endif
    </div>
    <div class="thread-info">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <span class="thread-name text-truncate" style="max-width: 150px;">{{ $userName }}</span>
            <span class="thread-meta">{{ $msgCreatedAt }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center">
            <span class="thread-last-msg">{{ $msgBody }}</span>
            @if ($isUnread)
                <span class="badge rounded-pill bg-primary" style="font-size: 0.6rem;">&nbsp;</span>
            @endif
        </div>
        @if ($isImportant)
            <small class="text-warning"><i class="fa-solid fa-star" style="font-size: 0.7rem;"></i></small>
        @endif
    </div>
</a>

