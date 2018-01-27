#!/usr/bin/perl
#
# Based on VMWare examples

use Data::Dumper;
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
   'vmalias' => {
      type => "=s",
      help => "The name of the virtual machine",
      required => 0,
   },
   'folder' => {
      type => "=s",
      help => "Folder where vm is stored",
      required => 0,
	  default => "SelfPortalVMs",
   },
   'datacenter' => {
      type => "=s",
      help => "Folder where vm is stored",
      required => 0,
	  default => "Altoros",
   },
);

Opts::add_options(%opts);
Opts::parse();

my @valid_properties;
my $filename;

Util::connect();
if (defined (Opts::get_option('vmname'))) { get_vm_info(); }
elsif (defined (Opts::get_option('vmalias'))) { get_vm_debug(); }
else { get_vms(); }
Util::disconnect();

sub get_vm_debug{
	my %filter_hash = create_hash(Opts::get_option('ipaddress'),
                              Opts::get_option('powerstatus'),
                              Opts::get_option('guestos'));
	my $vm_views = VMUtils::get_vms ('VirtualMachine',
                                      Opts::get_option ('vmname'),
                                      Opts::get_option ('datacenter'),
                                      Opts::get_option ('folder'),
                                      Opts::get_option ('pool'),
                                      Opts::get_option ('host'),
                                     %filter_hash);
   my $vm_view = shift @$vm_views;
   if ($vm_view) {
		print $vm_view->summary->config->uuid;
	}

}

sub get_vm_info {
   my $vm_views = Vim::find_entity_views(
   		view_type => 'VirtualMachine',
		filter => {
			'config.uuid' => Opts::get_option ('vmname')
		}
   );
   my $vm_view = shift @$vm_views;
   if ($vm_view) {
		my %vm = (
				'ID' => $vm_view->summary->config->uuid,
				'name' => $vm_view->name,
				'vcpus' => $vm_view->summary->config->numCpu,
				'ram' => $vm_view->summary->config->memorySizeMB,
				'disk' => ($vm_view->summary->storage->unshared+$vm_view->summary->storage->uncommitted)/1073741824,
				'image' => $vm_view->summary->config->guestFullName,
				'addresses' => $vm_view->guest->ipAddress,
				'status' => $vm_view->summary->runtime->powerState->val,
		);
		print encode_json \%vm;
	}
}


sub get_vms {
   my %filter_hash = create_hash(Opts::get_option('ipaddress'),
                              Opts::get_option('powerstatus'),
                              Opts::get_option('guestos'));

   my $vm_views = VMUtils::get_vms ('VirtualMachine',
                                      Opts::get_option ('vmname'),
                                      Opts::get_option ('datacenter'),
                                      Opts::get_option ('folder'),
                                      Opts::get_option ('pool'),
                                      Opts::get_option ('host'),
                                     %filter_hash);


	if ($vm_views) {
		my @vms=();
	  	foreach my $vm_view(@$vm_views)
		{
			my %vm = (
				'ID' => $vm_view->summary->config->uuid,
				'Name' => $vm_view->name,
				'Image Name' => $vm_view->summary->config->guestFullName,
				'Networks' => $vm_view->guest->ipAddress,
				'Status' => $vm_view->summary->runtime->powerState->val,
			);
			push (@vms,\%vm);
		}
		print encode_json \@vms;
	}
}

sub create_hash {
   my ($ipaddress, $powerstatus, $guestos) = @_;
   my %filter_hash;
   if ($ipaddress) {
      $filter_hash{'guest.ipAddress'} = $ipaddress;
   }
   if ($powerstatus) {
      $filter_hash{'runtime.powerState'} = $powerstatus;
   }
   # bug 299213
   if ($guestos) {
      # bug 456626
      $filter_hash{'config.guestFullName'} = qr/^\Q$guestos\E$/i;
   }
   return %filter_hash;
}


__END__
