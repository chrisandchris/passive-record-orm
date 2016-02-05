# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.box = "chrisandchris/amp"
    config.vm.network :private_network, type: "dhcp"
    config.vm.synced_folder ".", "/vagrant"
    config.vm.provision :shell, :path => "bootstrap.sh"
end
