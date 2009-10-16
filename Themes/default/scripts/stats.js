var doingExpandCollapse = false;

function smfStats_year(uniqueId, initialState)
{
	this.uid = uniqueId;
	this.onBeforeCollapse = handleBeforeCollapse;
	this.yearToggle = new smc_Toggle({
		bToggleEnabled: true,
		bCurrentlyCollapsed: initialState,
		instanceRef: this,
		funcOnBeforeCollapse: this.onBeforeCollapse,
		aSwappableContainers: [
		],
		aSwapImages: [
			{
				sId: 'year_img_' +  uniqueId,
				srcExpanded: smf_images_url + '/collapse.gif',
				altExpanded: '-',
				srcCollapsed: smf_images_url + '/expand.gif',
				altCollapsed: '+'
			}
		],
		aSwapLinks: [
			{
				sId: 'year_link_' +  uniqueId,
				msgExpanded: uniqueId,
				msgCollapsed: uniqueId
			}
		]
	});
	this.monthElements = new Array();

	this.toggle = toggleYear;
	this.addMonth = addMonthToYear;
	this.addDay = YearaddDayToMonth;
	this.toggleMonth = ToggleMonth;

	function handleBeforeCollapse()
	{
		if (!this.bCollapsed)
		{
			for (m in this.opt.instanceRef.monthElements)
				if (!this.opt.instanceRef.monthElements[m].toggleElement.bCollapsed)
					this.opt.instanceRef.monthElements[m].toggle();
		}
	}

	function toggleYear()
	{
		// Are we closing this down?
		if (this.yearToggle.bCollapsed)
		{
			for (m in this.monthElements)
				if (!this.monthElements[m].toggleElement.bCollapsed)
					this.monthElements[m].toggle();
		}
		this.yearToggle.toggle();
	}

	function addMonthToYear(monthid, monthState)
	{
		this.yearToggle.opt.aSwappableContainers[this.yearToggle.opt.aSwappableContainers.length] = 'tr_month_' + monthid;
		this.monthElements[monthid] = new smfStats_month(monthid, monthState);
	}

	function YearaddDayToMonth(monthid, dayid)
	{
		this.monthElements[monthid].daysloaded = true;
		this.monthElements[monthid].addDay(dayid);
	}

	function ToggleMonth(monthid)
	{
		return this.monthElements[monthid].toggle();
	}
}

function smfStats_month(uniqueId, initialState)
{
	this.uid = uniqueId;
	this.mode = initialState;
	this.daysloaded = !initialState;

	var aLink = document.getElementById('m' +  uniqueId);

	this.onBeforeCollapse = handleBeforeCollapse;
	this.onBeforeExpand = handleBeforeExpand;
	this.toggleElement = new smc_Toggle({
		bToggleEnabled: true,
		bCurrentlyCollapsed: initialState,
		instanceRef: this,
		funcOnBeforeCollapse: this.onBeforeCollapse,
		funcOnBeforeExpand: this.onBeforeExpand,
		aSwappableContainers: [
		],
		aSwapImages: [
			{
				sId: 'img_' + uniqueId,
				srcExpanded: smf_images_url + '/collapse.gif',
				altExpanded: '-',
				srcCollapsed: smf_images_url + '/expand.gif',
				altCollapsed: '+'
			}
		],
		aSwapLinks: [
			{
				sId: 'm' +  uniqueId,
				msgExpanded: getInnerHTML(aLink),
				msgCollapsed: getInnerHTML(aLink)
			}
		]
	});

	this.addDay = addDayToMonth;

	function handleBeforeExpand()
	{
		if ('XMLHttpRequest' in window)
		{
			if (this.opt.instanceRef.daysloaded == false)
			{
				getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=stats;expand=" + this.opt.instanceRef.uid + ";xml", onDocReceived);
				doingExpandCollapse = true;
				if (typeof(window.ajax_indicator) == "function")
					ajax_indicator(true);
			}
			else
			{
				// If we are collapsing this make sure to tell the forum we don't need to load that data any more.
				if (this.bCollapsed)
				{
					getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=stats;expand=" + this.opt.instanceRef.uid + ";xml");
				}
			}
			return false;
		}
		else
		{
			return true;
		}

	}

	function handleBeforeCollapse()
	{
		if ('XMLHttpRequest' in window)
		{
			if (!this.bCollapsed)
			{
				getXMLDocument(smf_prepareScriptUrl(smf_scripturl) + "action=stats;collapse=" + this.opt.instanceRef.uid + ";xml");
			}
			return false;
		}
		else
		{
			return true;
		}
	}

	function addDayToMonth(id)
	{
		if (this.toggleElement.bCollapsed)
		{
			this.toggleElement.toggle();
		}
		this.toggleElement.opt.aSwappableContainers[this.toggleElement.opt.aSwappableContainers.length] = 'tr_day_' + id;
	}
}

function onDocReceived(XMLDoc)
{
	var numMonths = XMLDoc.getElementsByTagName("month").length, i, j, k, numDays, curDay, start, year;
	var myTable = document.getElementById("stats"), curId, myRow, myCell, myData;
	var dataCells = [
		"date",
		"new_topics",
		"new_posts",
		"new_members",
		"most_members_online"
	];

	if (numMonths > 0 && XMLDoc.getElementsByTagName("month")[0].getElementsByTagName("day").length > 0 && XMLDoc.getElementsByTagName("month")[0].getElementsByTagName("day")[0].getAttribute("hits") != null)
		dataCells[5] = "hits";

	for (i = 0; i < numMonths; i++)
	{
		numDays = XMLDoc.getElementsByTagName("month")[i].getElementsByTagName("day").length;
		curId = XMLDoc.getElementsByTagName("month")[i].getAttribute("id");
		start = document.getElementById("tr_month_" + curId).rowIndex + 1;
		year = curId.substr(0,4);

		for (j = 0; j < numDays; j++)
		{
			curDay = XMLDoc.getElementsByTagName("month")[i].getElementsByTagName("day")[j];
			myRow = myTable.insertRow(start + j);
			myRow.className = "windowbg2";
			myRow.id = "tr_day_" + curDay.getAttribute("date");
			yearElements[year].addDay(curId, curDay.getAttribute("date"));

			for (k in dataCells)
			{
				myCell = myRow.insertCell(-1);
				if (dataCells[k] == "date")
					myCell.style.paddingLeft = "6ex";
				else
					myCell.style.textAlign = "center";
				myData = document.createTextNode(curDay.getAttribute(dataCells[k]));
				myCell.appendChild(myData);
			}
		}
		// Adjust the link to collapse instead of expand
		document.getElementById("m" + curId).href = smf_prepareScriptUrl(smf_scripturl) + "action=stats;collapse=" + curId + "#" + curId;
	}

	doingExpandCollapse = false;
	if (typeof(window.ajax_indicator) == "function")
		ajax_indicator(false);

};