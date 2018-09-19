// from http://www.kryogenix.org/code/browser/sorttable/
// used and modifed by VegasInsider.com, Inc. as allowed by the MIT licence
addEvent(window, "load", sortables_init);

var SORT_COLUMN_INDEX;

function sortables_init() 
{
	// assume 1 header row unless told otherwise
	if (typeof sortHdrRows == "undefined") 
	{
		sortHdrRows = 1;
	}
	if (typeof sortFtrRows == "undefined")
	{
		sortFtrRows = 0;
	}
	if (typeof sortOdds == "undefined")
	{
		sortOdds = 0;
	}
    // Find all tables with class sortable and make them sortable
    if (!document.getElementsByTagName) return;
    tbls = document.getElementsByTagName("table");
    for (ti=0;ti<tbls.length;ti++)
    {
        thisTbl = tbls[ti];
        if (((' '+thisTbl.className+' ').indexOf("sortable") != -1) && (thisTbl.id))
        {
            //initTable(thisTbl.id);
            ts_makeSortable(thisTbl);
        }
    }
}

function ts_makeSortable(table)
{
    if (table.rows && table.rows.length > sortHdrRows)
    {
        var firstRow = table.rows[sortHdrRows-1];
    }
    if (!firstRow) return;
    
    // We have a first row: assume it's the header, and make its contents clickable links
    for (var i=0;i<firstRow.cells.length;i++)
    {
        var cell = firstRow.cells[i];
        var txt = ts_getInnerText(cell);
        cell.innerHTML = '<a href="#" class="sortheader" onclick="ts_resortTable(this);return false;">'+txt+'<span class="sortarrow">&nbsp;&nbsp;&nbsp;</span></a>';
	}
}

function ts_getInnerText(el)
{
	if (typeof el == "string") return el;
	if (typeof el == "undefined") { return el };
	if (el.innerText) return el.innerText;	//Not needed but it is faster
	if (el.innerHTML)
	{
		// try to just get text portion, without html
		// as in X from <b>X</b>
		var tmp = el.innerHTML;
		tmp = tmp.replace(/.*>(.*?)<.*/,"$1");
		return tmp;
	}

	var str = "";
	
	var cs = el.childNodes;
	var l = cs.length;
	for (var i = 0; i < l; i++)
	{
		switch (cs[i].nodeType)
		{
			case 1: //ELEMENT_NODE
				str += ts_getInnerText(cs[i]);
				break;
			case 3:	//TEXT_NODE
				str += cs[i].nodeValue;
				break;
		}
	}
	return str;
}

function ts_resortTable(lnk)
{
    // get the span
    var span;
    for (var ci=0;ci<lnk.childNodes.length;ci++)
    {
        if (lnk.childNodes[ci].tagName && lnk.childNodes[ci].tagName.toLowerCase() == 'span') span = lnk.childNodes[ci];
    }
    var td = lnk.parentNode;
    var column = td.cellIndex;
    var table = getParent(td,'TABLE');
    
    // Work out a type for the column
    if (table.rows.length <= sortHdrRows) return;
    var itm = 	ts_getInnerText(table.rows[sortHdrRows].cells[column]);

    sortfn = ts_sort_caseinsensitive;
    if (itm.match(/^\d\d[\/-]\d\d[\/-]\d\d\d\d$/)) sortfn = ts_sort_date;
    if (itm.match(/^\d\d[\/-]\d\d[\/-]\d\d$/)) sortfn = ts_sort_date;
    // if (itm.match(/^[�$]/)) sortfn = ts_sort_currency;
    if (itm.match(/^[\d\.]+$/)) sortfn = ts_sort_numeric;
    // special case of '-' meaning a zero number
    // or -number or +number or .number
    if (itm.match(/^-$/) || itm.match(/^-\d/) ||
    	itm.match(/^\+\d/) || itm.match(/^\.\d/)) sortfn = ts_sort_numeric;
    // "5-2" or "5 - 2" for team records require special sort
    if (itm.match(/^\d+ ?- ?\d+/)) sortfn = ts_sort_record;
    // "9-2" odds sort
    if (itm.match(/^\d+ ?- ?\d+/) && sortOdds) sortfn = ts_sort_odds;
    if (sortOdds && table.rows[sortHdrRows].cells[column].className == 'odds')  sortfn = ts_sort_odds;
    SORT_COLUMN_INDEX = column;
    var newRows = new Array();
    var ftrRows = new Array();
    var l = 0;
    for (j=sortHdrRows;j<table.rows.length-sortFtrRows;j++) { newRows[l++] = table.rows[j]; }
    if (sortFtrRows)
    {
	    l = 0;
	    for (j=table.rows.length-sortFtrRows;j<table.rows.length;j++) { ftrRows[l++] = table.rows[j]; }
	}
    newRows.sort(sortfn);

    if (span.getAttribute("sortdir") == 'up')
    {
        ARROW = '&nbsp;&darr;';
        newRows.reverse();
        span.setAttribute('sortdir','down');
    } 
    else
    {
        ARROW = '&nbsp;&uarr;';
        span.setAttribute('sortdir','up');
    }
    
    // We appendChild rows that already exist to the tbody, so it moves them rather than creating new ones
    for (i=0;i<newRows.length;i++) { table.tBodies[0].appendChild(newRows[i]); }
    // put footer back
    for (i=0;i<ftrRows.length;i++) { table.tBodies[0].appendChild(ftrRows[i]); }
    
    // Delete any other arrows there may be showing
    var allspans = document.getElementsByTagName("span");
    for (var ci=0;ci<allspans.length;ci++)
    {
        if (allspans[ci].className == 'sortarrow')
        {
            if (getParent(allspans[ci],"table") == getParent(lnk,"table"))
            { // in the same table as us?
                allspans[ci].innerHTML = '&nbsp;&nbsp;&nbsp;';
            }
        }
    }
        
    span.innerHTML = ARROW;
}

function getParent(el, pTagName)
{
	if (el == null) return null;
	else if (el.nodeType == 1 && el.tagName.toLowerCase() == pTagName.toLowerCase())	// Gecko bug, supposed to be uppercase
		return el;
	else
		return getParent(el.parentNode, pTagName);
}
function ts_sort_date(a,b)
{
    // y2k notes: two digit years less than 50 are treated as 20XX, greater than 50 are treated as 19XX
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]);
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]);
    if (aa.length == 10)
    {
        dt1 = aa.substr(6,4)+aa.substr(0,2)+aa.substr(3,2);
    } 
    else
    {
        yr = aa.substr(6,2);
        if (parseInt(yr) < 50) { yr = '20'+yr; } else { yr = '19'+yr; }
        dt1 = yr+aa.substr(0,2)+aa.substr(3,2);
    }
    if (bb.length == 10)
    {
        dt2 = bb.substr(6,4)+bb.substr(0,2)+bb.substr(3,2);
    }
    else
    {
        yr = bb.substr(6,2);
        if (parseInt(yr) < 50) { yr = '20'+yr; } else { yr = '19'+yr; }
        dt2 = yr+bb.substr(0,2)+bb.substr(3,2);
    }
    if (dt1==dt2) return 0;
    if (dt1<dt2) return 1;
    return -1;
}

function ts_sort_record(a,b)
{
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]);
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]);
    
    a_arr = aa.split('-');
    b_arr = bb.split('-');
	if (a_arr[0] != b_arr[0])
	{
		return parseFloat(b_arr[0]) - parseFloat(a_arr[0]);
	}
    return parseFloat(a_arr[1]) - parseFloat(b_arr[1]);
}

function ts_sort_odds(a,b)
{
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]);
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]);

    // handle "scratched" odds
    if (aa.toLowerCase().indexOf('scr') == 0)
    {
	    aa = '99999-1';
    }
    if (bb.toLowerCase().indexOf('scr') == 0)
    {
	    bb = '99999-1';
    }
    a_arr = aa.split('-');
    b_arr = bb.split('-');
    return (parseFloat(a_arr[0])/parseFloat(a_arr[1])) - (parseFloat(b_arr[0])/parseFloat(b_arr[1]));
}

//function ts_sort_currency(a,b)
//{ 
//    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]).replace(/[^0-9.]/g,'');
//    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]).replace(/[^0-9.]/g,'');
//    return parseFloat(bb) - parseFloat(aa);
//}

function ts_sort_numeric(a,b)
{  
    aa = parseFloat(ts_getInnerText(a.cells[SORT_COLUMN_INDEX]));
    if (isNaN(aa)) aa = 0;
    bb = parseFloat(ts_getInnerText(b.cells[SORT_COLUMN_INDEX])); 
    if (isNaN(bb)) bb = 0;
    return bb-aa;
}

function ts_sort_caseinsensitive(a,b)
{
    aa = ts_getInnerText(a.cells[SORT_COLUMN_INDEX]).toLowerCase();
    bb = ts_getInnerText(b.cells[SORT_COLUMN_INDEX]).toLowerCase();
    if (aa==bb) return 0;
    if (aa<bb) return 1;
    return -1;
}

function addEvent(elm, evType, fn, useCapture)
// addEvent and removeEvent
// cross-browser event handling for IE5+,  NS6 and Mozilla
// By Scott Andrew
{
  if (elm.addEventListener)
  {
    elm.addEventListener(evType, fn, useCapture);
    return true;
  }
  else if (elm.attachEvent)
  {
    var r = elm.attachEvent("on"+evType, fn);
    return r;
  }
// else
//{
//	alert("Handler could not be added");
//}
} 
