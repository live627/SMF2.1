function UnitTest(oOptions)
{
	this.opt = oOptions;
	this.aCurTests = [];
	this.iThreadsOpen = 0;
	this.iMaxThreads = 4;
	this.aMultiThreadedTests = []
	this.init();
}

UnitTest.prototype.init = function ()
{
	this.oMouseOver = new MouseOver({});

	this.buildTable();
};

UnitTest.prototype.buildTable = function ()
{
	var oTargetDiv = document.getElementById(this.opt.sTargetDivId);
	var sTable = '<a href="#" onclick="return ' + this.opt.sSelf + '.applyTest(null, null);">[Test all]</a><br /><br />';

	for (var i = 0, n = this.opt.aTests.length; i < n; i++)
	{
		if (this.opt.aTests[i].bIsMultiThreadSafe)
			this.aMultiThreadedTests[this.aMultiThreadedTests.length] = this.opt.aTests[i].sId;

		sTable += '<table border="0" cellspacing="1" cellpadding="4" align="center" width="100%" class="bordercolor" style="margin-bottom: 10px;"><tr class="titlebg" style="padding-top: 5px;"><td colspan="3">Test "' + this.opt.aTests[i].sId + '" <a href="#" onclick="return ' + this.opt.sSelf + '.applyTest(\'' + this.opt.aTests[i].sId + '\', null);">[Test group]</a></td></tr><tr class="catbg3"><td>Name</td><td>Action</td><td>Result</td></tr>';

		for (var j = 0, m = this.opt.aTests[i].aSubTests.length; j < m; j++)
		{
			sTable += '<tr class="windowbg"><td onmouseover="' + this.opt.sSelf + '.oMouseOver.showDescription(\'' + this.opt.aTests[i].aSubTests[j].sName + '\', \'' + this.opt.aTests[i].aSubTests[j].sDescription.replace(/\\/g, '\\\\').replace(/'/g, '\\\'').replace(/\n/g, '<br />') + '\')" onmouseout="' + this.opt.sSelf + '.oMouseOver.hideDescription()">' + this.opt.aTests[i].aSubTests[j].sName + '<div class="error smalltext" id="error_' + this.opt.aTests[i].sId + '-' + this.opt.aTests[i].aSubTests[j].sId + '" style="display: none; width: 90%;"></div></td><td><a href="#" onclick="return ' + this.opt.sSelf + '.applyTest(\'' + this.opt.aTests[i].sId + '\', \'' + this.opt.aTests[i].aSubTests[j].sId + '\');">[Test me]</a></td><td><div id="img_placeholder_' + this.opt.aTests[i].sId + '-' + this.opt.aTests[i].aSubTests[j].sId + '"></div></td></tr>';
		}

		sTable += '</table>';
	}

	setInnerHTML(oTargetDiv, sTable);

};

UnitTest.prototype.applyTest = function (sTestId, sSubTestId)
{
	this.aCurTests = [];
	for (var i = 0, n = this.opt.aTests.length; i < n; i++)
	{
		if (sTestId == null || this.opt.aTests[i].sId == sTestId)
		{
			for (var j = 0, m = this.opt.aTests[i].aSubTests.length; j < m; j++)
			{
				if (sSubTestId == null || sSubTestId == this.opt.aTests[i].aSubTests[j].sId)
				{
					this.aCurTests[this.aCurTests.length] = this.opt.aTests[i].sId + '-' + this.opt.aTests[i].aSubTests[j].sId;
				}
			}
		}
	}

	this.checkTest();
	setInterval(this.opt.sSelf + '.checkTest();', 1000);

	// Cancle the click.
	return false;
}

UnitTest.prototype.checkTest = function()
{
	if (this.aCurTests.length > 0 && this.iThreadsOpen < this.iMaxThreads)
	{
		for (var i in this.aCurTests)
		{
			var aTestParts = this.aCurTests[i].split('-');

			if (this.iThreadsOpen == 0 || in_array(aTestParts[0], this.aMultiThreadedTests))
			{
				var oImage = document.getElementById('img_placeholder_' + aTestParts[0] + '-' + aTestParts[1]);
				setInnerHTML(oImage, '<img src="' + smf_images_url + '/icons/field_check.gif" alt="" />');

				this.tmpMethod = getXMLDocument;
				this.tmpMethod(this.opt.sScriptUrl + '?sa=test;test=' + aTestParts[0] + ';subtest=' + aTestParts[1] + ';xml', this.onTestReady);
				delete tmpMethod;

				delete this.aCurTests[i];
				this.iThreadsOpen++;

				break;
			}
		}
	}
}


UnitTest.prototype.onTestReady = function (oXmlDoc)
{
	var aResults = oXmlDoc.getElementsByTagName('results')[0].getElementsByTagName('result');

	this.iThreadsOpen--;
	this.checkTest();

	for (var i = 0, n = aResults.length; i < n; i++)
	{
		var sTestId = aResults[i].getAttribute('test_id');
		var sSubTestId = aResults[i].getAttribute('sub_test_id');
		var bResult = aResults[i].getAttribute('passed') == '1';
		var oImage = document.getElementById('img_placeholder_' + sTestId + '-' + sSubTestId);
		var oErrorDiv = document.getElementById('error_' + sTestId + '-' + sSubTestId);

		if (bResult)
		{
			setInnerHTML(oImage, '<img src="' + smf_images_url + '/icons/field_valid.gif" alt="" />');
			oErrorDiv.style.display = 'none';
		}
		else
		{
			setInnerHTML(oImage, '<img src="' + smf_images_url + '/icons/field_invalid.gif" alt="" />');

			oErrorDiv.style.display = 'block';
			var sError = '';
			for (var j = 0, m = aResults[i].childNodes.length; j < m; j++)
				sError += aResults[i].childNodes[j].nodeValue.replace(/\n/g, '<br />')
			setInnerHTML(oErrorDiv, sError);
		}
	}
}

function MouseOver(oOptions)
{
	this.opt = oOptions;
	this.oDiv = null;
	this.init();
}

MouseOver.prototype.init = function()
{
	// Yes it's ugly this way, I know.
	this.oDiv = document.createElement('div');
	this.oDiv.className = 'smalltext';
	this.oDiv.style.visibility = 'hidden';
	this.oDiv.style.zIndex = '200';
	this.oDiv.style.position = 'absolute';
	this.oDiv.style.width = '200px';
	this.oDiv.style.backgroundColor = '#fffff0';
	this.oDiv.style.border = '1px dotted blue';
	this.oDiv.style.padding = '2px';
	document.body.appendChild(this.oDiv);

	this.oTitleDiv = document.createElement('div');
	this.oTitleDiv.style.fontWeight = 'bold';
	this.oTitleDiv.style.backgroundColor = '#f0f0f0';
	this.oDiv.appendChild(this.oTitleDiv);

	this.oContentDiv = document.createElement('div');
	this.oDiv.appendChild(this.oContentDiv);
}

MouseOver.prototype.showDescription = function(sTitle, sDescription)
{
	setInnerHTML(this.oTitleDiv, sTitle);
	setInnerHTML(this.oContentDiv, sDescription);
	this.oDiv.style.visibility = 'visible';

	document.MouseOver_instanceRef = this;
	document.onmousemove = function(oEvent) {
		document.MouseOver_instanceRef.mouseMove(oEvent ? oEvent : window.event);
	};
}

MouseOver.prototype.hideDescription = function()
{
	this.oDiv.style.visibility = 'hidden';
	document.onmousemove = '';
}

MouseOver.prototype.mouseMove = function(oEvent)
{
	if (oEvent.pageX)
	{
		this.oDiv.style.left = (oEvent.pageX + 20) + 'px';
		this.oDiv.style.top = (oEvent.pageY  + 20) + 'px';
	}
	else
	{
		this.oDiv.style.left = oEvent.clientX + (document.documentElement.scrollLeft ? document.documentElement.scrollLeft : document.body.scrollLeft) + 20;
		this.oDiv.style.top = oEvent.clientY + (document.documentElement.scrollTop ? document.documentElement.scrollTop : document.body.scrollTop) + 20;
	}
}

