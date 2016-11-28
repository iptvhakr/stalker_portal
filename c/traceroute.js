/**
 * Traceroute to hard coded domains (domain1, domain2).
 * Send result to statistic server.
 */
parent.sett_traceroute_start = function() {
    var resultPre = document.createElement('div'), gSTB = parent.gSTB,
        stbDownloadManager = parent.stbDownloadManager;
        downloads = JSON.parse(stbDownloadManager.GetQueueInfo()),
        loadingQueue = [],
        date = new Date(),
        endDate = new Date(),
        minutes = date.getMinutes(),
        time = parent.configuration.traceroute.time || 0,
        domains = parent.configuration.traceroute.domains,
        endMinutes, i, len;

    resultPre.setAttribute('tabindex', 0); // set tabindex attribute, we need to focus this element at the end of traceroute

    endDate.setSeconds(date.getSeconds() + (time * domains.length) + 60);
    if ( minutes < 10 ) {
        minutes = '0' + minutes;
    }
    endMinutes = endDate.getMinutes();
    if ( endMinutes < 10 ) {
        endMinutes = '0' + endMinutes;
    }

    // save all active jobs, because mtr can ruin downloads
    for ( i = 0, len = downloads.length; i < len; ++i ) {
        if ( downloads[i].state === 1 || downloads[i].state === 2 ) {
            loadingQueue.push(downloads[i]);
            stbDownloadManager.StopJob(downloads[i].id);
        }
    }
    SettingsPage.FileList.layer = SettingsPage.FileList.layers.Traceroute;

    SettingsPage.BCrumb.Push('', 'traceroute.png', _('Test result'));

    elclear(SettingsPage.FileList.handle);
    SettingsPage.FileList.handle.appendChild(resultPre);
    new CModalHint(currCPage, _('Perform diagnostics') + '...' +
    '<br>' + _('Start') + ': ' + date.getHours() + ':' + minutes +
    '<br>' + _('Estimated time of completion') + ': ' + endDate.getHours() + ':' + endMinutes, 1500);

    // hack to run mtr before browser render all page
    setTimeout(function () { // run mtr
        var table = element('table', {className: 'netanalyzeTable'}),
            handlerLink = SettingsPage.EventHandler,
            count = domains.length,
            results = '',
            i, result;

        SettingsPage.EventHandler = function() {};

        table.appendChild(element('tr', {}, [
            element('th', {}, _('IP Address')),
            element('th', {}, _('Loss')),
            element('th', {}, _('Ping'))
        ]));

        if ( time ) {
            for ( i = 0; i < count; ++i ) {
                result = gSTB.RDir('mtr --report --no-dns --report-cycles ' + time + ' ' + domains[i]);
                results += 'Traceroute to domain: ' + domains[i] + '\n' + result + '\n';
            }
            echo(result, 'result');
            // parsing mtr output
            result = result.split('\n');
            result.shift();
            result.shift();
            result.pop();
            result.forEach(function ( item ) {
                var data = item.trim().replace(/\s{2,}/g, ' ').split(' ');
                table.appendChild(element('tr', {}, [
                    element('td', {}, data[1]),
                    element('td', {}, data[2]),
                    element('td', {}, '' + Math.round(data[5]))
                ]));
            });
            sendStatistic(results);
        }

        resultPre.innerHTML = '';
        resultPre.appendChild(table);
        // restore active downloads
        for ( i = 0, len = loadingQueue.length; i < len; ++i ) {
            stbDownloadManager.StartJob(loadingQueue[i].id);
        }
        // hack to restore event handler, browser pass events with delay
        setTimeout(function () {
            SettingsPage.EventHandler = handlerLink;
            resultPre.focus();
        }, 0);
    },0);
}