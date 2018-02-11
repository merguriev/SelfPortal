#!/usr/bin/perl
#
# Based on VMWare examples

use strict;
use warnings;
use JSON;
use lib "/usr/lib/vmware-vcli/apps";
use IO::Socket::SSL;
use VMware::VIRuntime;
use AppUtil::VMUtil;

# Ignore SSL warnings or invalid server warning
$ENV{'PERL_LWP_SSL_VERIFY_HOSTNAME'} = 0;

IO::Socket::SSL::set_ctx_defaults(
     SSL_verifycn_scheme => 'www',
     SSL_verify_mode => 0,
);

$Util::script_version = "1.0";

sub create_hash;
sub get_vm_info;

my %field_values = (
   'vmname'  => 'vmname',
   'numCpu'  =>  'numCpu',
   'memorysize' => 'memorysize' ,
   'virtualdisks' => 'virtualdisks',
   'template' => 'template',
   'vmPathName'=> 'vmPathName',
   'guestFullName'=> 'guestFullName',
   'guestId' => 'guestId',
   'hostName' => 'hostName',
   'ipAddress' => 'ipAddress',
   'toolsStatus' => 'toolsStatus',
   'overallCpuUsage' => 'overallCpuUsage',
   'hostMemoryUsage'=> 'hostMemoryUsage',
   'guestMemoryUsage'=> 'guestMemoryUsage',
   'overallStatus' => 'overallStatus',
);

my %opts = (
   'vmname' => {
      type => "=s",
      help => "The name of the virtual machine",
      required => 0,
   },
);

Opts::add_options(%opts);
Opts::parse();

my @valid_properties;
my $filename;

Util::connect();
get_console_url();
Util::disconnect();

sub get_console_url {
   my $vm_views = Vim::find_entity_views(
   		view_type => 'VirtualMachine',
		filter => {
			'config.uuid' => Opts::get_option ('vmname')
		}
   );
   my $vm_view = shift @$vm_views;
   if ($vm_view) {

		my $ticket2 = $vm_view->AcquireTicket(ticketType => 'webmks');
		my $esxi = $ticket2->host;
		my $esxi_port = $ticket2->port;
		my $ticket = $ticket2->ticket;

		my %url = ("host" => $esxi, "port" => $esxi_port, "ticket" => $ticket, "url" => "../plugins/vspherevnc.html");
		print encode_json \%url;

	}

}

__END__
