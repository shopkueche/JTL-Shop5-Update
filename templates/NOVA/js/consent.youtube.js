document.addEventListener('consent.ready', function(e) {
	embedYoutube(e.detail);
});
document.addEventListener('consent.updated', function(e) {
	embedYoutube(e.detail);
});
function embedYoutube(detail) {
	if (detail !== null && typeof detail.youtube !== 'undefined' && detail.youtube === true) {
		let embeds = document.querySelectorAll('iframe.needs-consent.youtube');
		for (let i = 0; i < embeds.length; ++i) {
			embeds[i].src = embeds[i].dataset.src;
			embeds[i].className = 'youtube';
		}
		let notices = document.querySelectorAll('a[data-consent="youtube"]');
		for (let j = 0; j < notices.length; ++j) {
			notices[j].classList.add('d-none');
		}
	}
	if (detail !== null && typeof detail.vimeo !== 'undefined' && detail.vimeo === true) {
		let embeds = document.querySelectorAll('iframe.needs-consent.vimeo');
		for (let i = 0; i < embeds.length; ++i) {
			embeds[i].src = embeds[i].dataset.src;
			embeds[i].className = 'vimeo';
		}
		let notices = document.querySelectorAll('a[data-consent="vimeo"]');
		for (let j = 0; j < notices.length; ++j) {
			notices[j].classList.add('d-none');
		}
	}
}
