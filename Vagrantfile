# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure(2) do |config|
    config.vm.box = "ubuntu/trusty32"
    config.vm.network :private_network, ip: "192.168.80.2"
    config.vm.network :forwarded_port, guest: 80, host: 8000
    config.vm.provision :shell, :path => "bootstrap.sh"
    config.vm.synced_folder ".", "/vagrant", :nfs => { :mount_options => ['dmode=755','fmode=644'] }
end
