
// toggle accordion row active class
$('.accordion [data-parent]').on('show.bs.collapse', function () {
	$(this).parents('.accordion-row').addClass('active')
}).on('hide.bs.collapse', function () {
	$(this).parents('.accordion-row').removeClass('active')
})
