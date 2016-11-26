<?php
	class CpuRam {
		private $wmi;

		public function __construct() {
			if (!stristr(PHP_OS, 'win'))
				die('Do not support');
			$this->wmi = new COM("winmgmts:{impersonationLevel=impersonate}//./root/cimv2");
		}

		private function WMIQuery($query)
		{
			return $this->wmi->ExecQuery($query);
		}

		public function MemoryInfo()
		{
			foreach ($this->WMIQuery("SELECT TotalPhysicalMemory FROM Win32_ComputerSystem") as $wmis) {
				$total = $wmis->TotalPhysicalMemory;
				break;
			}

			foreach ($this->WMIQuery("SELECT AvailableBytes FROM Win32_PerfFormattedData_PerfOS_Memory") as $wmis) {
				$free = $wmis->AvailableBytes;
				break;
			}
			return array('free'=>$free,'use'=>($total-$free),'total'=>$total,);
		}

		public function CPUInfo()
		{
			foreach ($this->WMIQuery("SELECT PercentProcessorTime FROM Win32_PerfFormattedData_PerfOS_Processor") as $wmis) {
				$percent = $wmis->PercentProcessorTime;
				break;
			}
			foreach ($this->WMIQuery("SELECT Architecture, Name FROM Win32_Processor") as $wmis) {
				$name = $wmis->Name;
				break;
			}
			return array('percent'=>$percent,'name'=>$name);
		}

		public function Uptime() {
			foreach ($this->WMIQuery("SELECT LastBootUpTime FROM Win32_OperatingSystem") as $wmis) {
				$boot = floor($wmis->LastBootUpTime);
				break;
			}

			$booted = array(
				'year' => substr($boot, 0, 4),
				'month' => substr($boot, 4, 2),
				'day' => substr($boot, 6, 2),
				'hour' => substr($boot, 8, 2),
				'minute' => substr($boot, 10, 2),
				'second' => substr($boot, 12, 2)
			);

			$bootTime = mktime($booted['hour'], $booted['minute'], $booted['second'], $booted['month'], $booted['day'], $booted['year']);

			$uptime = (time() - $bootTime);
			return array('uptime'=>$uptime,'boottime'=>$boot);
		}
	}

	$a = new CpuRam();
	$mem = $a->MemoryInfo();
	$cpu = $a->CPUInfo();
	$uptime = $a->Uptime();

	echo 'Memory : '.$mem['free'].'/'.$mem['total'].'<br>'.
	     'CPU : '.$cpu['name'].' ('.$cpu['percent'].'%)<br>'.
	     'Uptime : '.$uptime['uptime'].' Seconds';
