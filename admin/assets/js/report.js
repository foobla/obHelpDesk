function fslink ( linkParam, linkValue )
{
  document.forms["obhelpdesk_report"].elements["linkparam"].value = linkParam ;
  document.forms["obhelpdesk_report"].elements["linkvalue"].value = linkValue ;
  document.forms["obhelpdesk_report"].elements["from_timeframe"].value = 0 ;
  document.forms["obhelpdesk_report"].submit();
  if (linkParam=="export") {
	document.forms["obhelpdesk_report"].elements["export"].value = '' ;
	document.forms["obhelpdesk_report"].elements["linkparam"].value = '' ;
	document.forms["obhelpdesk_report"].elements["linkvalue"].value = '' ;
  }
}