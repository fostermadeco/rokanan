# -*- mode: ruby -*-

require 'yaml'

dir = File.dirname(File.expand_path(__FILE__))
vars = YAML.load_file("#{dir}/ansible/host_vars/vagrant")

Vagrant.configure("2") do |config|

  config.vm.box_version = '20180903.0.0'
  config.vm.box_check_update = false

  config.vm.box = 'ubuntu/bionic64'
  config.disksize.size = '64GB'

  config.vm.hostname = vars["hostname"]
  config.vm.network "private_network", ip: vars["private_address"]

  config.hostsupdater.aliases = [ "#{vars['email_hostname']}" ] + (vars['server_aliases']||[]) + (vars['additional_vhosts']||[])

  config.ssh.username = "vagrant"
  config.ssh.private_key_path = ["~/.ssh/id_rsa", "~/.vagrant.d/insecure_private_key"]
  config.ssh.insert_key = false
  config.ssh.keys_only = false
  config.ssh.forward_agent = true

  config.vm.provision "file", source: "~/.ssh/id_rsa.pub", destination: "~/.ssh/authorized_keys"
  config.vm.synced_folder ".", "/var/www/#{vars['hostname']}", type: "nfs", :nfs => true, :mount_options => ['actimeo=2']

  config.vm.define vars['hostname']

  config.vm.provider "virtualbox" do |virtualbox|
    virtualbox.memory = 1024
    virtualbox.cpus = 2
    virtualbox.name = vars["hostname"]
  end

  config.vm.provision "ansible" do |ansible|
    ansible.verbose = "v"
    ansible.inventory_path = "ansible/hosts"
    ansible.limit = "vagrant"
    ansible.playbook = "ansible/provision_vagrant.yaml"
    ansible.extra_vars = {
      ansible_python_interpreter: "/usr/bin/python3"
    }
  end

end
