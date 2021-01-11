setNavActive();
if (window.location.href.indexOf('/podcasts') > 0) {
	window.addEventListener('popstate', function (e) {
		// console.log(e);
		if (e.state) {
			page = getPageFromUrl(e.state.href);
			// console.log('pushing '+page);
			showEpisodeContainer(page, false);
		}
	});
	$(document).ready(function () {
		page = getPageFromUrl(window.location.href);
		showEpisodeContainer(page, false);
		$('.page-link').click(function (e) {
			e.preventDefault();
			showEpisodeContainer($(this).attr('href').replace('#', ''), true);
			window.scrollTo(0, 0);
		});
	});
	function showEpisodeContainer(page, push_state) {
		// console.log('page entered: '+page);
		let prev_page = Math.max(1, parseInt(page) - 1);
		// console.log(prev_page);
		let next_page = Math.min($('.page-link').length - 2, parseInt(page) + 1);
		// console.log(next_page);
		$('.episode-container').hide();
		$('#episodes-container-' + page).show();
		$('.page-item').removeClass('active');
		$('.page-num[href="#' + page + '"]').parent().addClass('active');
		$('#prev-page').attr('href', '#' + prev_page);
		$('#next-page').attr('href', '#' + next_page);
		let href = window.location.href.replace(window.location.hash, '') + '#' + page;
		if (push_state) {
			window.location = href;
		}
	}
	function getPageFromUrl(url) {
		// console.log(url);
		page = 1;
		if (url.indexOf('#') > 0) {
			page = url.substring(url.indexOf('#') + 1);
		}
		// console.log(page);
		return page;
	}
}
function setNavActive() {
	// console.log('setting active');
	if ($('main.home').length) {
		$('.nav-link[href="/"]').parent().addClass('active');
	} else if ($('main.episodes-page').length) {
		$('.nav-link[href="/podcasts"]').parent().addClass('active');
	} else if ($('main.about').length) {
		$('.nav-link[href="/about"]').parent().addClass('active');
	} else if ($('main.hire').length) {
		$('.nav-link[href="/hire"]').parent().addClass('active');
	}
}