# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.box = "ge/amp7"
    config.vm.network :private_network, type: "dhcp"
    config.vm.synced_folder ".", "/vagrant", type: "nfs"
    config.vm.provision :shell, :path => "bootstrap.sh"
end
