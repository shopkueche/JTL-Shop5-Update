
// set filename in file fields
$('.custom-file-input').on('change', function() {
	let fileName = $(this).val().split('\\').pop()
	$(this).find('~ .custom-file-label span').html(fileName)
})
