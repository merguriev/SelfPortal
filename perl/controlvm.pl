#!/usr/bin/perl
#
# Based on VMWare examples

use strict;
use warnings;
use Switch;
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

sub start_vm;
sub stop_vm;
sub delete_vm;

my %opts = (
   'vmname' => {
      type => "=s",
      help => "The ID of the virtual machine",
      required => 1,
   },
   'action' => {
      type => "=s",
      help => "Start, Stop, Restart, Destroy",
      required => 1,
   },
   'datacenter' => {
      type => "=s",
      help => "The name of the virtual machine",
      required => 0,
   },
);

Opts::add_options(%opts);
Opts::parse();

if (defined (Opts::get_option('vmname')))
{
	Util::connect();
	switch (Opts::get_option('action'))
	{
		case "Start" { start_vm(); }
		case "Stop" { stop_vm(); }
		case "Restart" { stop_vm(); start_vm(); }
		case "Destroy" { stop_vm(); delete_vm(); }
	}
	Util::disconnect();
}

sub start_vm {

	my $vm_views = Vim::find_entity_views(
   		view_type => 'VirtualMachine',
		filter => {
			'config.uuid' => Opts::get_option ('vmname')
		}
   	);
   	my $vm_view = shift @$vm_views;
   	if ($vm_view) { $vm_view->PowerOnVM(); }
}

sub stop_vm {

	my $vm_views = Vim::find_entity_views(
   		view_type => 'VirtualMachine',
		filter => {
			'config.uuid' => Opts::get_option ('vmname')
		}
   	);
   	my $vm_view = shift @$vm_views;
   	if ($vm_view) { eval {$vm_view->PowerOffVM();} }
}

sub delete_vm {

	my $vm_views = Vim::find_entity_views(
   		view_type => 'VirtualMachine',
		filter => {
			'config.uuid' => Opts::get_option ('vmname')
		}
   	);
   	my $vm_view = shift @$vm_views;
   	if ($vm_view) { $vm_view->Destroy(); }
}
