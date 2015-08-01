# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.provider "parallels" do |v|
      v.memory = 1024
    end
    config.vm.box = "parallels/ubuntu-14.04"
    config.vm.network :private_network, ip: "192.168.50.5"
    config.vm.network :forwarded_port, guest: 80, host: 8000
    config.vm.provision :shell, :path => "bootstrap.sh"
    config.vm.synced_folder ".", "/vagrant"
end
