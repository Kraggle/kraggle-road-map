const $ = jQuery;

$(() => {
	$(window).on('resize', doResize);
	doResize();

	$('.nft.unloaded').each(function() {
		const link = $(this).data('link');
		$.getJSON(link, json => {
			$(this).data('info', json);
			$(this).html($('<img />', {
				src: json.image,
				alt: ''
			}));
			$(this).removeClass('unloaded');
		});
	});

	$('.nft-grid').on('click', '.nft:not(.unloaded)', function() {
		$('.nft-splash').removeClass('hide');
		const $pop = $('.nft-popup'),
			data = $(this).data('info');
		$('.nft-title', $pop).html(`#${data.edition} | ${data.name.replace(/\s#\d+$/, '')}`);
		$('.nft-desc', $pop).html(data.description);
		$('.nft-collection', $pop).html(data.collection.name);
		$('.nft-edition', $pop).html(data.edition);
		$('.nft-img', $pop).attr('src', data.image);
		const $attrs = $('.nft-attrs.more', $pop);
		$attrs.html('');
		$.each(data.attributes, (i, attr) => {
			$attrs.append(`<span class="nft-attr">
				<strong>${attr.trait_type}: </strong>
				<span>${attr.value}</span>
			</span>`);
		});
	});

	$('.nft-splash, .nft-popup .nft-close').on('click', () => {
		$('.nft-splash').addClass('hide');
	})
});

const doResize = () => {
	const n = Math.floor($(window).width() / 220);
	$('.nft-grid').css('grid-template-columns', `repeat(${n}, 1fr)`)
}