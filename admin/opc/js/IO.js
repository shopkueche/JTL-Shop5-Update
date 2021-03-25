class IO
{
    init()
    {
        return Promise.all([
            new Promise((res, rej) => ioCall('opcGetIOFunctionNames', [], res, rej))
                .then(names => this.generateIoFunctions(names)),
            new Promise((res, rej) => ioCall('opcGetPageIOFunctionNames', [], res, rej))
                .then(names => this.generateIoFunctions(names)),
        ]);
    }

    generateIoFunctions(names)
    {
        names.forEach(name => {
            this[name] = this.generateIoFunction('opc' + capitalize(name));
        });
    }

    generateIoFunction(publicName)
    {
        return function(...args) {
            let jqxhr   = null;
            let promise = new Promise((res, rej) => {
                jqxhr = ioCall(publicName, args, res, rej);
            });
            promise.jqxhr = jqxhr;
            return promise;
        };
    }

    createPortlet(portletClass)
    {
        return this.getPortletPreviewHtml({class: portletClass});
    }
}
