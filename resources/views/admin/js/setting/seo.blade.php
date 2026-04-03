<script>
	const permalinksJson = {!! $permalinksJson !!};
	const defaultPermalink = "{{ config('settings.seo.listing_permalink') }}";
	
	onDocumentReady((event) => {
		let listingHashedIdEnabledEl = document.querySelector("input[type=checkbox][name=listing_hashed_id_enabled]");
		if (listingHashedIdEnabledEl) {
			applyListingIdHashingActions(listingHashedIdEnabledEl);
			listingHashedIdEnabledEl.addEventListener("change", e => applyListingIdHashingActions(e.target));
		}
		
		/* Block Bots */
		let blockBotsEnabledEl = document.querySelector("input[type=checkbox][name=block_bots_enabled]");
		if (blockBotsEnabledEl) {
			toggleBotsFields(blockBotsEnabledEl);
			blockBotsEnabledEl.addEventListener("change", e => toggleBotsFields(e.target));
		}
	});
	
	function applyListingIdHashingActions(listingHashedIdEnabledEl) {
		if (!listingHashedIdEnabledEl) return;
		
		const listingPermalinkEl = document.querySelector("select[name=listing_permalink].select2_from_array");
		if (!listingPermalinkEl) return;
		
		const updatedPermalinks = updatePermalinksValues(permalinksJson, listingHashedIdEnabledEl.checked);
		updateSelect2Options(listingPermalinkEl, updatedPermalinks, defaultPermalink);
	}
	
	function updatePermalinksValues(jsonObject, isHashable = true) {
		const hashablePattern = /{hashableId}/g;
		const idPattern = /{id}/g;
		
		const newObject = {};
		for (let key in jsonObject) {
			if (jsonObject.hasOwnProperty(key)) {
				let newValue;
				
				if (isHashable) {
					newValue = jsonObject[key].replace(idPattern, '{hashableId}');
				} else {
					newValue = jsonObject[key].replace(hashablePattern, '{id}');
				}
				
				newObject[key] = newValue;
			}
		}
		
		return newObject;
	}
	
	function toggleBotsFields(blockBotsEnabledEl) {
		if (!blockBotsEnabledEl) return;
		
		if (blockBotsEnabledEl.checked) {
			setElementsVisibility("show", ".block-bots-el");
		} else {
			setElementsVisibility("hide", ".block-bots-el");
		}
	}
</script>
