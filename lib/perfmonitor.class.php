<?
class perfmonitor
{
function perfmonitor()
{
   $this->perf_data = array();
   $this->total_time = 0;
}

function getmicrotime(){
   list($usec, $sec) = explode(" ",microtime());
   return ((float)$usec + (float)$sec);
  }

 function StartMeasure($mpoint) {
  $tmp=$this->getmicrotime();
  $this->perf_data[$mpoint]['START']=$this->getmicrotime();
 }

 function EndMeasure($mpoint) {
  $this->perf_data[$mpoint]['END']=$this->getmicrotime();
  $this->perf_data[$mpoint]['TIME']=$this->perf_data[$mpoint]['END']-$this->perf_data[$mpoint]['START'];
  $this->total_time+=$this->perf_data[$mpoint]['TIME'];
  $this->perf_data[$mpoint]['NUM']++;
 }

 function PerformanceReport($hidden=1) {
  $rep .= "<!-- BEGIN PERFORMANCE REPORT\n";
  foreach ($this->perf_data as $k => $v) {
  if ($this->total_time) {
   $v['PROC']=((int)($v['TIME']/$this->total_time*100*100))/100;
  }
  $tmp[]="$k (".$v['NUM']."): ".$v['TIME']." ".$v['PROC']."%";
  }
  $rep .= implode("\n", $tmp);
  $rep .= "\n END PERFORMANCE REPORT -->";
  if ($hidden)
     echo $rep;
  else
     echo nl2br(htmlspecialchars($rep));
 }
}
?>