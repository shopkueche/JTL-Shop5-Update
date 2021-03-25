class PreviewFrame
{
    init()
    {
        installGuiElements(this, [
            'previewPanel',
            'previewFrame',
            'previewForm',
            'previewPageDataInput',
        ]);
    }

    showPreview(pageFullUrl, draftData)
    {
        this.previewPageDataInput
            .val(draftData);

        this.previewForm
            .attr('action', pageFullUrl)
            .submit();

        this.previewFrame
            .contents().find('body').html('');

        this.previewPanel.show();
    }
}
