# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "larryli/utopic32"
  config.vm.host_name = "tcf-dev"
  config.vm.network :forwarded_port, host: 8081, guest: 80
  config.vm.synced_folder ".", "/vagrant"
  config.ssh.forward_agent = true
  config.vm.provision :shell, :path => "provisioning/scripts/bootstrap-puppet.sh"
  config.vm.provision :puppet do |puppet|
    puppet.manifests_path = 'provisioning/puppet/manifests'
    puppet.manifest_file = 'site.pp'
    puppet.module_path = 'provisioning/puppet/modules'
  end
  config.vm.provider "virtualbox" do |v|
    v.memory = 1024
  end
end
