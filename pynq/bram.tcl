
################################################################
# This is a generated script based on design: bram
#
# Though there are limitations about the generated script,
# the main purpose of this utility is to make learning
# IP Integrator Tcl commands easier.
################################################################

namespace eval _tcl {
proc get_script_folder {} {
   set script_path [file normalize [info script]]
   set script_folder [file dirname $script_path]
   return $script_folder
}
}
variable script_folder
set script_folder [_tcl::get_script_folder]

################################################################
# Check if script is running in correct Vivado version.
################################################################
set scripts_vivado_version 2024.1
set current_vivado_version [version -short]

if { [string first $scripts_vivado_version $current_vivado_version] == -1 } {
   puts ""
   if { [string compare $scripts_vivado_version $current_vivado_version] > 0 } {
      catch {common::send_gid_msg -ssname BD::TCL -id 2042 -severity "ERROR" " This script was generated using Vivado <$scripts_vivado_version> and is being run in <$current_vivado_version> of Vivado. Sourcing the script failed since it was created with a future version of Vivado."}

   } else {
     catch {common::send_gid_msg -ssname BD::TCL -id 2041 -severity "ERROR" "This script was generated using Vivado <$scripts_vivado_version> and is being run in <$current_vivado_version> of Vivado. Please run the script in Vivado <$scripts_vivado_version> then open the design in Vivado <$current_vivado_version>. Upgrade the design by running \"Tools => Report => Report IP Status...\", then run write_bd_tcl to create an updated script."}

   }

   return 1
}

################################################################
# START
################################################################

# To test this script, run the following commands from Vivado Tcl console:
# source bram_script.tcl

# If there is no project opened, this script will create a
# project, but make sure you do not have an existing project
# <./myproj/project_1.xpr> in the current working folder.

set list_projs [get_projects -quiet]
if { $list_projs eq "" } {
   create_project project_1 myproj -part xc7z020clg400-1
   set_property BOARD_PART www.digilentinc.com:pynq-z1:part0:1.0 [current_project]
}


# CHANGE DESIGN NAME HERE
variable design_name
set design_name bram

# If you do not already have an existing IP Integrator design open,
# you can create a design using the following command:
#    create_bd_design $design_name

# Creating design if needed
set errMsg ""
set nRet 0

set cur_design [current_bd_design -quiet]
set list_cells [get_bd_cells -quiet]

if { ${design_name} eq "" } {
   # USE CASES:
   #    1) Design_name not set

   set errMsg "Please set the variable <design_name> to a non-empty value."
   set nRet 1

} elseif { ${cur_design} ne "" && ${list_cells} eq "" } {
   # USE CASES:
   #    2): Current design opened AND is empty AND names same.
   #    3): Current design opened AND is empty AND names diff; design_name NOT in project.
   #    4): Current design opened AND is empty AND names diff; design_name exists in project.

   if { $cur_design ne $design_name } {
      common::send_gid_msg -ssname BD::TCL -id 2001 -severity "INFO" "Changing value of <design_name> from <$design_name> to <$cur_design> since current design is empty."
      set design_name [get_property NAME $cur_design]
   }
   common::send_gid_msg -ssname BD::TCL -id 2002 -severity "INFO" "Constructing design in IPI design <$cur_design>..."

} elseif { ${cur_design} ne "" && $list_cells ne "" && $cur_design eq $design_name } {
   # USE CASES:
   #    5) Current design opened AND has components AND same names.

   set errMsg "Design <$design_name> already exists in your project, please set the variable <design_name> to another value."
   set nRet 1
} elseif { [get_files -quiet ${design_name}.bd] ne "" } {
   # USE CASES: 
   #    6) Current opened design, has components, but diff names, design_name exists in project.
   #    7) No opened design, design_name exists in project.

   set errMsg "Design <$design_name> already exists in your project, please set the variable <design_name> to another value."
   set nRet 2

} else {
   # USE CASES:
   #    8) No opened design, design_name not in project.
   #    9) Current opened design, has components, but diff names, design_name not in project.

   common::send_gid_msg -ssname BD::TCL -id 2003 -severity "INFO" "Currently there is no design <$design_name> in project, so creating one..."

   create_bd_design $design_name

   common::send_gid_msg -ssname BD::TCL -id 2004 -severity "INFO" "Making design <$design_name> as current_bd_design."
   current_bd_design $design_name

}

common::send_gid_msg -ssname BD::TCL -id 2005 -severity "INFO" "Currently the variable <design_name> is equal to \"$design_name\"."

if { $nRet != 0 } {
   catch {common::send_gid_msg -ssname BD::TCL -id 2006 -severity "ERROR" $errMsg}
   return $nRet
}

set bCheckIPsPassed 1
##################################################################
# CHECK IPs
##################################################################
set bCheckIPs 1
if { $bCheckIPs == 1 } {
   set list_check_ips "\ 
xilinx.com:ip:processing_system7:5.5\
xilinx.com:ip:proc_sys_reset:5.0\
xilinx.com:ip:smartconnect:1.0\
spicer.local:user:Decim5:1.0\
spicer.local:user:filter_ctrl:1.0\
spicer.local:user:Decim2:1.0\
spicer.local:user:Decim4:1.0\
spicer.local:user:anchor:1.0\
spicer.local:user:ADAQ4001:1.0\
"

   set list_ips_missing ""
   common::send_gid_msg -ssname BD::TCL -id 2011 -severity "INFO" "Checking if the following IPs exist in the project's IP catalog: $list_check_ips ."

   foreach ip_vlnv $list_check_ips {
      set ip_obj [get_ipdefs -all $ip_vlnv]
      if { $ip_obj eq "" } {
         lappend list_ips_missing $ip_vlnv
      }
   }

   if { $list_ips_missing ne "" } {
      catch {common::send_gid_msg -ssname BD::TCL -id 2012 -severity "ERROR" "The following IPs are not found in the IP Catalog:\n  $list_ips_missing\n\nResolution: Please add the repository containing the IP(s) to the project." }
      set bCheckIPsPassed 0
   }

}

if { $bCheckIPsPassed != 1 } {
  common::send_gid_msg -ssname BD::TCL -id 2023 -severity "WARNING" "Will not continue with creation of design due to the error(s) above."
  return 3
}

##################################################################
# DESIGN PROCs
##################################################################


# Hierarchical cell: ADAQ4001
proc create_hier_cell_ADAQ4001 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_ADAQ4001() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins

  # Create pins
  create_bd_pin -dir I -type clk clk
  create_bd_pin -dir I -type rst reset_n
  create_bd_pin -dir O data_ready
  create_bd_pin -dir O -from 15 -to 0 data_out
  create_bd_pin -dir I sdo_13
  create_bd_pin -dir O data_ready1
  create_bd_pin -dir O -from 15 -to 0 data_out1
  create_bd_pin -dir I sdo_14
  create_bd_pin -dir O data_ready2
  create_bd_pin -dir O -from 15 -to 0 data_out2
  create_bd_pin -dir I sdo_15
  create_bd_pin -dir O data_ready3
  create_bd_pin -dir O -from 15 -to 0 data_out3
  create_bd_pin -dir I sdo_5
  create_bd_pin -dir O data_ready4
  create_bd_pin -dir O -from 15 -to 0 data_out4
  create_bd_pin -dir O cnv
  create_bd_pin -dir O sck
  create_bd_pin -dir I sdo_0
  create_bd_pin -dir O data_ready5
  create_bd_pin -dir O -from 15 -to 0 data_out5
  create_bd_pin -dir I sdo_6
  create_bd_pin -dir I sdo_1
  create_bd_pin -dir O data_ready6
  create_bd_pin -dir O -from 15 -to 0 data_out6
  create_bd_pin -dir I sdo_7
  create_bd_pin -dir O data_ready7
  create_bd_pin -dir O -from 15 -to 0 data_out7
  create_bd_pin -dir I sdo_8
  create_bd_pin -dir O data_ready8
  create_bd_pin -dir O -from 15 -to 0 data_out8
  create_bd_pin -dir I sdo_3
  create_bd_pin -dir O data_ready9
  create_bd_pin -dir O -from 15 -to 0 data_out9
  create_bd_pin -dir I sdo_9
  create_bd_pin -dir O data_ready10
  create_bd_pin -dir O -from 15 -to 0 data_out10
  create_bd_pin -dir I sdo_10
  create_bd_pin -dir O data_ready11
  create_bd_pin -dir I sdo_4
  create_bd_pin -dir O data_ready12
  create_bd_pin -dir O -from 15 -to 0 data_out11
  create_bd_pin -dir I sdo_11
  create_bd_pin -dir O data_ready13
  create_bd_pin -dir O -from 15 -to 0 data_out12
  create_bd_pin -dir I sdo_12
  create_bd_pin -dir O data_ready14
  create_bd_pin -dir O -from 15 -to 0 data_out13
  create_bd_pin -dir I sdo_2
  create_bd_pin -dir O cnv1
  create_bd_pin -dir O data_ready15
  create_bd_pin -dir O cnv2

  # Create instance: ADAQ4001_7, and set properties
  set ADAQ4001_7 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_7 ]

  # Create instance: ADAQ4001_8, and set properties
  set ADAQ4001_8 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_8 ]

  # Create instance: ADAQ4001_9, and set properties
  set ADAQ4001_9 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_9 ]

  # Create instance: ADAQ4001_10, and set properties
  set ADAQ4001_10 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_10 ]

  # Create instance: ADAQ4001_0, and set properties
  set ADAQ4001_0 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_0 ]

  # Create instance: ADAQ4001_11, and set properties
  set ADAQ4001_11 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_11 ]

  # Create instance: ADAQ4001_1, and set properties
  set ADAQ4001_1 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_1 ]

  # Create instance: ADAQ4001_12, and set properties
  set ADAQ4001_12 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_12 ]

  # Create instance: ADAQ4001_13, and set properties
  set ADAQ4001_13 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_13 ]

  # Create instance: ADAQ4001_3, and set properties
  set ADAQ4001_3 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_3 ]

  # Create instance: ADAQ4001_14, and set properties
  set ADAQ4001_14 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_14 ]

  # Create instance: ADAQ4001_15, and set properties
  set ADAQ4001_15 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_15 ]

  # Create instance: ADAQ4001_4, and set properties
  set ADAQ4001_4 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_4 ]

  # Create instance: ADAQ4001_5, and set properties
  set ADAQ4001_5 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_5 ]

  # Create instance: ADAQ4001_6, and set properties
  set ADAQ4001_6 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_6 ]

  # Create instance: ADAQ4001_2, and set properties
  set ADAQ4001_2 [ create_bd_cell -type ip -vlnv spicer.local:user:ADAQ4001:1.0 ADAQ4001_2 ]

  # Create port connections
  connect_bd_net -net ADAQ4001_0_cnv [get_bd_pins ADAQ4001_0/cnv] [get_bd_pins cnv]
  connect_bd_net -net ADAQ4001_0_data_out [get_bd_pins ADAQ4001_0/data_out] [get_bd_pins data_out4]
  connect_bd_net -net ADAQ4001_0_data_ready [get_bd_pins ADAQ4001_0/data_ready] [get_bd_pins data_ready4]
  connect_bd_net -net ADAQ4001_0_sck [get_bd_pins ADAQ4001_0/sck] [get_bd_pins sck]
  connect_bd_net -net ADAQ4001_10_data_out [get_bd_pins ADAQ4001_10/data_out] [get_bd_pins data_out3]
  connect_bd_net -net ADAQ4001_10_data_ready [get_bd_pins ADAQ4001_10/data_ready] [get_bd_pins data_ready3]
  connect_bd_net -net ADAQ4001_11_data_out [get_bd_pins ADAQ4001_11/data_out] [get_bd_pins data_out5]
  connect_bd_net -net ADAQ4001_11_data_ready [get_bd_pins ADAQ4001_11/data_ready] [get_bd_pins data_ready5]
  connect_bd_net -net ADAQ4001_12_data_out [get_bd_pins ADAQ4001_12/data_out] [get_bd_pins data_out6]
  connect_bd_net -net ADAQ4001_12_data_ready [get_bd_pins ADAQ4001_12/data_ready] [get_bd_pins data_ready6]
  connect_bd_net -net ADAQ4001_13_data_out [get_bd_pins ADAQ4001_13/data_out] [get_bd_pins data_out7]
  connect_bd_net -net ADAQ4001_13_data_ready [get_bd_pins ADAQ4001_13/data_ready] [get_bd_pins data_ready7]
  connect_bd_net -net ADAQ4001_14_data_out [get_bd_pins ADAQ4001_14/data_out] [get_bd_pins data_out9]
  connect_bd_net -net ADAQ4001_14_data_ready [get_bd_pins ADAQ4001_14/data_ready] [get_bd_pins data_ready9]
  connect_bd_net -net ADAQ4001_15_data_out [get_bd_pins ADAQ4001_15/data_out] [get_bd_pins data_out10]
  connect_bd_net -net ADAQ4001_15_data_ready [get_bd_pins ADAQ4001_15/data_ready] [get_bd_pins data_ready10]
  connect_bd_net -net ADAQ4001_1_cnv [get_bd_pins ADAQ4001_1/cnv] [get_bd_pins cnv2]
  connect_bd_net -net ADAQ4001_1_data_ready [get_bd_pins ADAQ4001_1/data_ready] [get_bd_pins data_ready15]
  connect_bd_net -net ADAQ4001_2_data_out [get_bd_pins ADAQ4001_2/data_out] [get_bd_pins data_out13]
  connect_bd_net -net ADAQ4001_2_data_ready [get_bd_pins ADAQ4001_2/data_ready] [get_bd_pins data_ready14]
  connect_bd_net -net ADAQ4001_3_data_out [get_bd_pins ADAQ4001_3/data_out] [get_bd_pins data_out8]
  connect_bd_net -net ADAQ4001_3_data_ready [get_bd_pins ADAQ4001_3/data_ready] [get_bd_pins data_ready8]
  connect_bd_net -net ADAQ4001_4_cnv [get_bd_pins ADAQ4001_4/cnv] [get_bd_pins cnv1]
  connect_bd_net -net ADAQ4001_4_data_ready [get_bd_pins ADAQ4001_4/data_ready] [get_bd_pins data_ready11]
  connect_bd_net -net ADAQ4001_5_data_out [get_bd_pins ADAQ4001_5/data_out] [get_bd_pins data_out11]
  connect_bd_net -net ADAQ4001_5_data_ready [get_bd_pins ADAQ4001_5/data_ready] [get_bd_pins data_ready12]
  connect_bd_net -net ADAQ4001_6_data_out [get_bd_pins ADAQ4001_6/data_out] [get_bd_pins data_out12]
  connect_bd_net -net ADAQ4001_6_data_ready [get_bd_pins ADAQ4001_6/data_ready] [get_bd_pins data_ready13]
  connect_bd_net -net ADAQ4001_7_data_out [get_bd_pins ADAQ4001_7/data_out] [get_bd_pins data_out]
  connect_bd_net -net ADAQ4001_7_data_ready [get_bd_pins ADAQ4001_7/data_ready] [get_bd_pins data_ready]
  connect_bd_net -net ADAQ4001_8_data_out [get_bd_pins ADAQ4001_8/data_out] [get_bd_pins data_out1]
  connect_bd_net -net ADAQ4001_8_data_ready [get_bd_pins ADAQ4001_8/data_ready] [get_bd_pins data_ready1]
  connect_bd_net -net ADAQ4001_9_data_out [get_bd_pins ADAQ4001_9/data_out] [get_bd_pins data_out2]
  connect_bd_net -net ADAQ4001_9_data_ready [get_bd_pins ADAQ4001_9/data_ready] [get_bd_pins data_ready2]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins clk] [get_bd_pins ADAQ4001_0/clk] [get_bd_pins ADAQ4001_1/clk] [get_bd_pins ADAQ4001_2/clk] [get_bd_pins ADAQ4001_3/clk] [get_bd_pins ADAQ4001_12/clk] [get_bd_pins ADAQ4001_13/clk] [get_bd_pins ADAQ4001_14/clk] [get_bd_pins ADAQ4001_15/clk] [get_bd_pins ADAQ4001_4/clk] [get_bd_pins ADAQ4001_5/clk] [get_bd_pins ADAQ4001_6/clk] [get_bd_pins ADAQ4001_7/clk] [get_bd_pins ADAQ4001_8/clk] [get_bd_pins ADAQ4001_9/clk] [get_bd_pins ADAQ4001_10/clk] [get_bd_pins ADAQ4001_11/clk]
  connect_bd_net -net rst_ps7_0_100M_peripheral_aresetn [get_bd_pins reset_n] [get_bd_pins ADAQ4001_0/reset_n] [get_bd_pins ADAQ4001_1/reset_n] [get_bd_pins ADAQ4001_2/reset_n] [get_bd_pins ADAQ4001_3/reset_n] [get_bd_pins ADAQ4001_12/reset_n] [get_bd_pins ADAQ4001_13/reset_n] [get_bd_pins ADAQ4001_14/reset_n] [get_bd_pins ADAQ4001_15/reset_n] [get_bd_pins ADAQ4001_4/reset_n] [get_bd_pins ADAQ4001_5/reset_n] [get_bd_pins ADAQ4001_6/reset_n] [get_bd_pins ADAQ4001_7/reset_n] [get_bd_pins ADAQ4001_8/reset_n] [get_bd_pins ADAQ4001_9/reset_n] [get_bd_pins ADAQ4001_10/reset_n] [get_bd_pins ADAQ4001_11/reset_n]
  connect_bd_net -net sdo_0_1 [get_bd_pins sdo_0] [get_bd_pins ADAQ4001_0/sdo]
  connect_bd_net -net sdo_10_1 [get_bd_pins sdo_10] [get_bd_pins ADAQ4001_15/sdo]
  connect_bd_net -net sdo_11_1 [get_bd_pins sdo_11] [get_bd_pins ADAQ4001_5/sdo]
  connect_bd_net -net sdo_12_1 [get_bd_pins sdo_12] [get_bd_pins ADAQ4001_6/sdo]
  connect_bd_net -net sdo_13_1 [get_bd_pins sdo_13] [get_bd_pins ADAQ4001_7/sdo]
  connect_bd_net -net sdo_14_1 [get_bd_pins sdo_14] [get_bd_pins ADAQ4001_8/sdo]
  connect_bd_net -net sdo_15_1 [get_bd_pins sdo_15] [get_bd_pins ADAQ4001_9/sdo]
  connect_bd_net -net sdo_1_1 [get_bd_pins sdo_1] [get_bd_pins ADAQ4001_1/sdo]
  connect_bd_net -net sdo_2_1 [get_bd_pins sdo_2] [get_bd_pins ADAQ4001_2/sdo]
  connect_bd_net -net sdo_3_1 [get_bd_pins sdo_3] [get_bd_pins ADAQ4001_3/sdo]
  connect_bd_net -net sdo_4_1 [get_bd_pins sdo_4] [get_bd_pins ADAQ4001_4/sdo]
  connect_bd_net -net sdo_5_1 [get_bd_pins sdo_5] [get_bd_pins ADAQ4001_10/sdo]
  connect_bd_net -net sdo_6_1 [get_bd_pins sdo_6] [get_bd_pins ADAQ4001_11/sdo]
  connect_bd_net -net sdo_7_1 [get_bd_pins sdo_7] [get_bd_pins ADAQ4001_12/sdo]
  connect_bd_net -net sdo_8_1 [get_bd_pins sdo_8] [get_bd_pins ADAQ4001_13/sdo]
  connect_bd_net -net sdo_9_1 [get_bd_pins sdo_9] [get_bd_pins ADAQ4001_14/sdo]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_15
proc create_hier_cell_adc_15 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_15() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_14
proc create_hier_cell_adc_14 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_14() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_13
proc create_hier_cell_adc_13 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_13() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_12
proc create_hier_cell_adc_12 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_12() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_11
proc create_hier_cell_adc_11 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_11() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_10
proc create_hier_cell_adc_10 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_10() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_9
proc create_hier_cell_adc_9 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_9() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_8
proc create_hier_cell_adc_8 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_8() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_7
proc create_hier_cell_adc_7 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_7() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_6
proc create_hier_cell_adc_6 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_6() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_5
proc create_hier_cell_adc_5 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_5() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_4
proc create_hier_cell_adc_4 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_4() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_3
proc create_hier_cell_adc_3 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_3() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_2
proc create_hier_cell_adc_2 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_2() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_1
proc create_hier_cell_adc_1 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_1() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net s_axi_aresetn_1 [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}

# Hierarchical cell: adc_0
proc create_hier_cell_adc_0 { parentCell nameHier } {

  variable script_folder

  if { $parentCell eq "" || $nameHier eq "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2092 -severity "ERROR" "create_hier_cell_adc_0() - Empty argument(s)!"}
     return
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj

  # Create cell and set as current instance
  set hier_obj [create_bd_cell -type hier $nameHier]
  current_bd_instance $hier_obj

  # Create interface pins
  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI1

  create_bd_intf_pin -mode Slave -vlnv xilinx.com:interface:aximm_rtl:1.0 S_AXI


  # Create pins
  create_bd_pin -dir I -type clk s_axi_aclk
  create_bd_pin -dir I -type rst s_axi_aresetn
  create_bd_pin -dir I -from 15 -to 0 data_in
  create_bd_pin -dir I din_rdy

  # Create instance: Decim5_0, and set properties
  set Decim5_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_0 ]

  # Create instance: filter_ctrl_0, and set properties
  set filter_ctrl_0 [ create_bd_cell -type ip -vlnv spicer.local:user:filter_ctrl:1.0 filter_ctrl_0 ]

  # Create instance: Decim2_0, and set properties
  set Decim2_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim2:1.0 Decim2_0 ]
  set_property CONFIG.NUM_TAPS {49} $Decim2_0


  # Create instance: Decim4_0, and set properties
  set Decim4_0 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim4:1.0 Decim4_0 ]

  # Create instance: Decim5_1, and set properties
  set Decim5_1 [ create_bd_cell -type ip -vlnv spicer.local:user:Decim5:1.0 Decim5_1 ]

  # Create instance: anchor_0, and set properties
  set anchor_0 [ create_bd_cell -type ip -vlnv spicer.local:user:anchor:1.0 anchor_0 ]
  set_property -dict [list \
    CONFIG.C_S_AXI_ADDR_WIDTH {16} \
    CONFIG.TOTAL_SAMPLES {2500} \
  ] $anchor_0


  # Create interface connections
  connect_bd_intf_net -intf_net S_AXI_1 [get_bd_intf_pins S_AXI] [get_bd_intf_pins anchor_0/S_AXI]
  connect_bd_intf_net -intf_net ps7_0_axi_periph_M01_AXI [get_bd_intf_pins S_AXI1] [get_bd_intf_pins filter_ctrl_0/S_AXI]

  # Create port connections
  connect_bd_net -net AD7476A_0_data_a [get_bd_pins data_in] [get_bd_pins Decim2_0/data_in]
  connect_bd_net -net AD7476A_0_dout_a_rdy [get_bd_pins din_rdy] [get_bd_pins Decim2_0/din_rdy]
  connect_bd_net -net Decim2_0_data_out [get_bd_pins Decim2_0/data_out] [get_bd_pins Decim4_0/data_in]
  connect_bd_net -net Decim2_0_dout_rdy [get_bd_pins Decim2_0/dout_rdy] [get_bd_pins Decim4_0/din_rdy]
  connect_bd_net -net Decim4_0_data_out [get_bd_pins Decim4_0/data_out] [get_bd_pins Decim5_0/data_in]
  connect_bd_net -net Decim4_0_dout_rdy [get_bd_pins Decim4_0/dout_rdy] [get_bd_pins Decim5_0/din_rdy]
  connect_bd_net -net Decim5_0_data_out [get_bd_pins Decim5_0/data_out] [get_bd_pins Decim5_1/data_in]
  connect_bd_net -net Decim5_0_dout_rdy [get_bd_pins Decim5_0/dout_rdy] [get_bd_pins Decim5_1/din_rdy]
  connect_bd_net -net Decim5_1_data_out [get_bd_pins Decim5_1/data_out] [get_bd_pins anchor_0/channel]
  connect_bd_net -net Decim5_1_dout_rdy [get_bd_pins Decim5_1/dout_rdy] [get_bd_pins anchor_0/channel_rdy]
  connect_bd_net -net filter_ctrl_0_f1 [get_bd_pins filter_ctrl_0/f1] [get_bd_pins Decim2_0/en]
  connect_bd_net -net filter_ctrl_0_f2 [get_bd_pins filter_ctrl_0/f2] [get_bd_pins Decim4_0/en]
  connect_bd_net -net filter_ctrl_0_f3 [get_bd_pins filter_ctrl_0/f3] [get_bd_pins Decim5_0/en]
  connect_bd_net -net filter_ctrl_0_f4 [get_bd_pins filter_ctrl_0/f4] [get_bd_pins Decim5_1/en]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins s_axi_aclk] [get_bd_pins Decim2_0/clk] [get_bd_pins Decim4_0/clk] [get_bd_pins Decim5_0/clk] [get_bd_pins Decim5_1/clk] [get_bd_pins anchor_0/s_axi_aclk] [get_bd_pins filter_ctrl_0/s_axi_aclk]
  connect_bd_net -net rst_ps7_0_100M_peripheral_aresetn [get_bd_pins s_axi_aresetn] [get_bd_pins Decim2_0/reset_n] [get_bd_pins Decim4_0/reset_n] [get_bd_pins Decim5_0/reset_n] [get_bd_pins Decim5_1/reset_n] [get_bd_pins anchor_0/s_axi_aresetn] [get_bd_pins filter_ctrl_0/s_axi_aresetn]

  # Restore current instance
  current_bd_instance $oldCurInst
}


# Procedure to create entire design; Provide argument to make
# procedure reusable. If parentCell is "", will use root.
proc create_root_design { parentCell } {

  variable script_folder
  variable design_name

  if { $parentCell eq "" } {
     set parentCell [get_bd_cells /]
  }

  # Get object for parentCell
  set parentObj [get_bd_cells $parentCell]
  if { $parentObj == "" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2090 -severity "ERROR" "Unable to find parent cell <$parentCell>!"}
     return
  }

  # Make sure parentObj is hier blk
  set parentType [get_property TYPE $parentObj]
  if { $parentType ne "hier" } {
     catch {common::send_gid_msg -ssname BD::TCL -id 2091 -severity "ERROR" "Parent <$parentObj> has TYPE = <$parentType>. Expected to be <hier>."}
     return
  }

  # Save current instance; Restore later
  set oldCurInst [current_bd_instance .]

  # Set parent object as current
  current_bd_instance $parentObj


  # Create interface ports
  set DDR [ create_bd_intf_port -mode Master -vlnv xilinx.com:interface:ddrx_rtl:1.0 DDR ]

  set FIXED_IO [ create_bd_intf_port -mode Master -vlnv xilinx.com:display_processing_system7:fixedio_rtl:1.0 FIXED_IO ]


  # Create ports
  set cnv [ create_bd_port -dir O cnv ]
  set sck [ create_bd_port -dir O sck ]
  set sdo_0 [ create_bd_port -dir I sdo_0 ]
  set sdo_1 [ create_bd_port -dir I sdo_1 ]
  set sdo_2 [ create_bd_port -dir I sdo_2 ]
  set sdo_3 [ create_bd_port -dir I sdo_3 ]
  set sdo_4 [ create_bd_port -dir I sdo_4 ]
  set sdo_5 [ create_bd_port -dir I sdo_5 ]
  set sdo_6 [ create_bd_port -dir I sdo_6 ]
  set sdo_7 [ create_bd_port -dir I sdo_7 ]
  set sdo_8 [ create_bd_port -dir I sdo_8 ]
  set sdo_9 [ create_bd_port -dir I sdo_9 ]
  set sdo_10 [ create_bd_port -dir I sdo_10 ]
  set sdo_11 [ create_bd_port -dir I sdo_11 ]
  set sdo_12 [ create_bd_port -dir I sdo_12 ]
  set sdo_13 [ create_bd_port -dir I sdo_13 ]
  set sdo_14 [ create_bd_port -dir I sdo_14 ]
  set sdo_15 [ create_bd_port -dir I sdo_15 ]

  # Create instance: processing_system7_0, and set properties
  set processing_system7_0 [ create_bd_cell -type ip -vlnv xilinx.com:ip:processing_system7:5.5 processing_system7_0 ]
  set_property -dict [list \
    CONFIG.PCW_ACT_APU_PERIPHERAL_FREQMHZ {650.000000} \
    CONFIG.PCW_ACT_CAN0_PERIPHERAL_FREQMHZ {23.8095} \
    CONFIG.PCW_ACT_CAN1_PERIPHERAL_FREQMHZ {23.8095} \
    CONFIG.PCW_ACT_CAN_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_DCI_PERIPHERAL_FREQMHZ {10.096154} \
    CONFIG.PCW_ACT_ENET0_PERIPHERAL_FREQMHZ {125.000000} \
    CONFIG.PCW_ACT_ENET1_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_FPGA0_PERIPHERAL_FREQMHZ {100.000000} \
    CONFIG.PCW_ACT_FPGA1_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_FPGA2_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_FPGA3_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_I2C_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_ACT_PCAP_PERIPHERAL_FREQMHZ {200.000000} \
    CONFIG.PCW_ACT_QSPI_PERIPHERAL_FREQMHZ {200.000000} \
    CONFIG.PCW_ACT_SDIO_PERIPHERAL_FREQMHZ {50.000000} \
    CONFIG.PCW_ACT_SMC_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_SPI_PERIPHERAL_FREQMHZ {10.000000} \
    CONFIG.PCW_ACT_TPIU_PERIPHERAL_FREQMHZ {200.000000} \
    CONFIG.PCW_ACT_TTC0_CLK0_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_ACT_TTC0_CLK1_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_ACT_TTC0_CLK2_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_ACT_TTC1_CLK0_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_ACT_TTC1_CLK1_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_ACT_TTC1_CLK2_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_ACT_TTC_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_ACT_UART_PERIPHERAL_FREQMHZ {100.000000} \
    CONFIG.PCW_ACT_USB0_PERIPHERAL_FREQMHZ {60} \
    CONFIG.PCW_ACT_USB1_PERIPHERAL_FREQMHZ {60} \
    CONFIG.PCW_ACT_WDT_PERIPHERAL_FREQMHZ {108.333336} \
    CONFIG.PCW_APU_CLK_RATIO_ENABLE {6:2:1} \
    CONFIG.PCW_APU_PERIPHERAL_FREQMHZ {650} \
    CONFIG.PCW_CAN0_PERIPHERAL_CLKSRC {External} \
    CONFIG.PCW_CAN1_PERIPHERAL_CLKSRC {External} \
    CONFIG.PCW_CAN_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_CAN_PERIPHERAL_VALID {0} \
    CONFIG.PCW_CLK0_FREQ {100000000} \
    CONFIG.PCW_CLK1_FREQ {10000000} \
    CONFIG.PCW_CLK2_FREQ {10000000} \
    CONFIG.PCW_CLK3_FREQ {10000000} \
    CONFIG.PCW_CPU_CPU_6X4X_MAX_RANGE {667} \
    CONFIG.PCW_CPU_PERIPHERAL_CLKSRC {ARM PLL} \
    CONFIG.PCW_CRYSTAL_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_DCI_PERIPHERAL_CLKSRC {DDR PLL} \
    CONFIG.PCW_DCI_PERIPHERAL_FREQMHZ {10.159} \
    CONFIG.PCW_DDR_PERIPHERAL_CLKSRC {DDR PLL} \
    CONFIG.PCW_DDR_RAM_BASEADDR {0x00100000} \
    CONFIG.PCW_DDR_RAM_HIGHADDR {0x1FFFFFFF} \
    CONFIG.PCW_DM_WIDTH {4} \
    CONFIG.PCW_DQS_WIDTH {4} \
    CONFIG.PCW_DQ_WIDTH {32} \
    CONFIG.PCW_ENET0_BASEADDR {0xE000B000} \
    CONFIG.PCW_ENET0_ENET0_IO {MIO 16 .. 27} \
    CONFIG.PCW_ENET0_GRP_MDIO_ENABLE {1} \
    CONFIG.PCW_ENET0_GRP_MDIO_IO {MIO 52 .. 53} \
    CONFIG.PCW_ENET0_HIGHADDR {0xE000BFFF} \
    CONFIG.PCW_ENET0_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_ENET0_PERIPHERAL_ENABLE {1} \
    CONFIG.PCW_ENET0_PERIPHERAL_FREQMHZ {1000 Mbps} \
    CONFIG.PCW_ENET0_RESET_ENABLE {1} \
    CONFIG.PCW_ENET0_RESET_IO {MIO 9} \
    CONFIG.PCW_ENET1_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_ENET1_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_ENET_RESET_ENABLE {1} \
    CONFIG.PCW_ENET_RESET_POLARITY {Active Low} \
    CONFIG.PCW_ENET_RESET_SELECT {Share reset pin} \
    CONFIG.PCW_EN_4K_TIMER {0} \
    CONFIG.PCW_EN_CAN0 {0} \
    CONFIG.PCW_EN_CAN1 {0} \
    CONFIG.PCW_EN_CLK0_PORT {1} \
    CONFIG.PCW_EN_CLK1_PORT {0} \
    CONFIG.PCW_EN_CLK2_PORT {0} \
    CONFIG.PCW_EN_CLK3_PORT {0} \
    CONFIG.PCW_EN_CLKTRIG0_PORT {0} \
    CONFIG.PCW_EN_CLKTRIG1_PORT {0} \
    CONFIG.PCW_EN_CLKTRIG2_PORT {0} \
    CONFIG.PCW_EN_CLKTRIG3_PORT {0} \
    CONFIG.PCW_EN_DDR {1} \
    CONFIG.PCW_EN_EMIO_CAN0 {0} \
    CONFIG.PCW_EN_EMIO_CAN1 {0} \
    CONFIG.PCW_EN_EMIO_CD_SDIO0 {0} \
    CONFIG.PCW_EN_EMIO_CD_SDIO1 {0} \
    CONFIG.PCW_EN_EMIO_ENET0 {0} \
    CONFIG.PCW_EN_EMIO_ENET1 {0} \
    CONFIG.PCW_EN_EMIO_GPIO {0} \
    CONFIG.PCW_EN_EMIO_I2C0 {0} \
    CONFIG.PCW_EN_EMIO_I2C1 {0} \
    CONFIG.PCW_EN_EMIO_MODEM_UART0 {0} \
    CONFIG.PCW_EN_EMIO_MODEM_UART1 {0} \
    CONFIG.PCW_EN_EMIO_PJTAG {0} \
    CONFIG.PCW_EN_EMIO_SDIO0 {0} \
    CONFIG.PCW_EN_EMIO_SDIO1 {0} \
    CONFIG.PCW_EN_EMIO_SPI0 {0} \
    CONFIG.PCW_EN_EMIO_SPI1 {0} \
    CONFIG.PCW_EN_EMIO_SRAM_INT {0} \
    CONFIG.PCW_EN_EMIO_TRACE {0} \
    CONFIG.PCW_EN_EMIO_TTC0 {0} \
    CONFIG.PCW_EN_EMIO_TTC1 {0} \
    CONFIG.PCW_EN_EMIO_UART0 {0} \
    CONFIG.PCW_EN_EMIO_UART1 {0} \
    CONFIG.PCW_EN_EMIO_WDT {0} \
    CONFIG.PCW_EN_EMIO_WP_SDIO0 {0} \
    CONFIG.PCW_EN_EMIO_WP_SDIO1 {0} \
    CONFIG.PCW_EN_ENET0 {1} \
    CONFIG.PCW_EN_ENET1 {0} \
    CONFIG.PCW_EN_GPIO {1} \
    CONFIG.PCW_EN_I2C0 {0} \
    CONFIG.PCW_EN_I2C1 {0} \
    CONFIG.PCW_EN_MODEM_UART0 {0} \
    CONFIG.PCW_EN_MODEM_UART1 {0} \
    CONFIG.PCW_EN_PJTAG {0} \
    CONFIG.PCW_EN_PTP_ENET0 {0} \
    CONFIG.PCW_EN_PTP_ENET1 {0} \
    CONFIG.PCW_EN_QSPI {1} \
    CONFIG.PCW_EN_RST0_PORT {1} \
    CONFIG.PCW_EN_RST1_PORT {0} \
    CONFIG.PCW_EN_RST2_PORT {0} \
    CONFIG.PCW_EN_RST3_PORT {0} \
    CONFIG.PCW_EN_SDIO0 {1} \
    CONFIG.PCW_EN_SDIO1 {0} \
    CONFIG.PCW_EN_SMC {0} \
    CONFIG.PCW_EN_SPI0 {0} \
    CONFIG.PCW_EN_SPI1 {0} \
    CONFIG.PCW_EN_TRACE {0} \
    CONFIG.PCW_EN_TTC0 {0} \
    CONFIG.PCW_EN_TTC1 {0} \
    CONFIG.PCW_EN_UART0 {1} \
    CONFIG.PCW_EN_UART1 {0} \
    CONFIG.PCW_EN_USB0 {1} \
    CONFIG.PCW_EN_USB1 {0} \
    CONFIG.PCW_EN_WDT {0} \
    CONFIG.PCW_FCLK0_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_FCLK1_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_FCLK2_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_FCLK3_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_FCLK_CLK0_BUF {TRUE} \
    CONFIG.PCW_FPGA0_PERIPHERAL_FREQMHZ {100} \
    CONFIG.PCW_FPGA1_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_FPGA2_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_FPGA3_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_FPGA_FCLK0_ENABLE {1} \
    CONFIG.PCW_GPIO_BASEADDR {0xE000A000} \
    CONFIG.PCW_GPIO_EMIO_GPIO_ENABLE {0} \
    CONFIG.PCW_GPIO_HIGHADDR {0xE000AFFF} \
    CONFIG.PCW_GPIO_MIO_GPIO_ENABLE {1} \
    CONFIG.PCW_GPIO_MIO_GPIO_IO {MIO} \
    CONFIG.PCW_GPIO_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_I2C_RESET_ENABLE {1} \
    CONFIG.PCW_I2C_RESET_POLARITY {Active Low} \
    CONFIG.PCW_IMPORT_BOARD_PRESET {None} \
    CONFIG.PCW_INCLUDE_ACP_TRANS_CHECK {0} \
    CONFIG.PCW_MIO_0_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_0_PULLUP {enabled} \
    CONFIG.PCW_MIO_0_SLEW {slow} \
    CONFIG.PCW_MIO_10_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_10_PULLUP {enabled} \
    CONFIG.PCW_MIO_10_SLEW {slow} \
    CONFIG.PCW_MIO_11_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_11_PULLUP {enabled} \
    CONFIG.PCW_MIO_11_SLEW {slow} \
    CONFIG.PCW_MIO_12_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_12_PULLUP {enabled} \
    CONFIG.PCW_MIO_12_SLEW {slow} \
    CONFIG.PCW_MIO_13_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_13_PULLUP {enabled} \
    CONFIG.PCW_MIO_13_SLEW {slow} \
    CONFIG.PCW_MIO_14_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_14_PULLUP {enabled} \
    CONFIG.PCW_MIO_14_SLEW {slow} \
    CONFIG.PCW_MIO_15_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_15_PULLUP {enabled} \
    CONFIG.PCW_MIO_15_SLEW {slow} \
    CONFIG.PCW_MIO_16_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_16_PULLUP {enabled} \
    CONFIG.PCW_MIO_16_SLEW {slow} \
    CONFIG.PCW_MIO_17_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_17_PULLUP {enabled} \
    CONFIG.PCW_MIO_17_SLEW {slow} \
    CONFIG.PCW_MIO_18_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_18_PULLUP {enabled} \
    CONFIG.PCW_MIO_18_SLEW {slow} \
    CONFIG.PCW_MIO_19_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_19_PULLUP {enabled} \
    CONFIG.PCW_MIO_19_SLEW {slow} \
    CONFIG.PCW_MIO_1_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_1_PULLUP {enabled} \
    CONFIG.PCW_MIO_1_SLEW {slow} \
    CONFIG.PCW_MIO_20_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_20_PULLUP {enabled} \
    CONFIG.PCW_MIO_20_SLEW {slow} \
    CONFIG.PCW_MIO_21_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_21_PULLUP {enabled} \
    CONFIG.PCW_MIO_21_SLEW {slow} \
    CONFIG.PCW_MIO_22_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_22_PULLUP {enabled} \
    CONFIG.PCW_MIO_22_SLEW {slow} \
    CONFIG.PCW_MIO_23_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_23_PULLUP {enabled} \
    CONFIG.PCW_MIO_23_SLEW {slow} \
    CONFIG.PCW_MIO_24_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_24_PULLUP {enabled} \
    CONFIG.PCW_MIO_24_SLEW {slow} \
    CONFIG.PCW_MIO_25_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_25_PULLUP {enabled} \
    CONFIG.PCW_MIO_25_SLEW {slow} \
    CONFIG.PCW_MIO_26_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_26_PULLUP {enabled} \
    CONFIG.PCW_MIO_26_SLEW {slow} \
    CONFIG.PCW_MIO_27_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_27_PULLUP {enabled} \
    CONFIG.PCW_MIO_27_SLEW {slow} \
    CONFIG.PCW_MIO_28_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_28_PULLUP {enabled} \
    CONFIG.PCW_MIO_28_SLEW {slow} \
    CONFIG.PCW_MIO_29_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_29_PULLUP {enabled} \
    CONFIG.PCW_MIO_29_SLEW {slow} \
    CONFIG.PCW_MIO_2_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_2_SLEW {slow} \
    CONFIG.PCW_MIO_30_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_30_PULLUP {enabled} \
    CONFIG.PCW_MIO_30_SLEW {slow} \
    CONFIG.PCW_MIO_31_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_31_PULLUP {enabled} \
    CONFIG.PCW_MIO_31_SLEW {slow} \
    CONFIG.PCW_MIO_32_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_32_PULLUP {enabled} \
    CONFIG.PCW_MIO_32_SLEW {slow} \
    CONFIG.PCW_MIO_33_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_33_PULLUP {enabled} \
    CONFIG.PCW_MIO_33_SLEW {slow} \
    CONFIG.PCW_MIO_34_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_34_PULLUP {enabled} \
    CONFIG.PCW_MIO_34_SLEW {slow} \
    CONFIG.PCW_MIO_35_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_35_PULLUP {enabled} \
    CONFIG.PCW_MIO_35_SLEW {slow} \
    CONFIG.PCW_MIO_36_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_36_PULLUP {enabled} \
    CONFIG.PCW_MIO_36_SLEW {slow} \
    CONFIG.PCW_MIO_37_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_37_PULLUP {enabled} \
    CONFIG.PCW_MIO_37_SLEW {slow} \
    CONFIG.PCW_MIO_38_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_38_PULLUP {enabled} \
    CONFIG.PCW_MIO_38_SLEW {slow} \
    CONFIG.PCW_MIO_39_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_39_PULLUP {enabled} \
    CONFIG.PCW_MIO_39_SLEW {slow} \
    CONFIG.PCW_MIO_3_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_3_SLEW {slow} \
    CONFIG.PCW_MIO_40_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_40_PULLUP {enabled} \
    CONFIG.PCW_MIO_40_SLEW {slow} \
    CONFIG.PCW_MIO_41_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_41_PULLUP {enabled} \
    CONFIG.PCW_MIO_41_SLEW {slow} \
    CONFIG.PCW_MIO_42_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_42_PULLUP {enabled} \
    CONFIG.PCW_MIO_42_SLEW {slow} \
    CONFIG.PCW_MIO_43_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_43_PULLUP {enabled} \
    CONFIG.PCW_MIO_43_SLEW {slow} \
    CONFIG.PCW_MIO_44_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_44_PULLUP {enabled} \
    CONFIG.PCW_MIO_44_SLEW {slow} \
    CONFIG.PCW_MIO_45_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_45_PULLUP {enabled} \
    CONFIG.PCW_MIO_45_SLEW {slow} \
    CONFIG.PCW_MIO_46_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_46_PULLUP {enabled} \
    CONFIG.PCW_MIO_46_SLEW {slow} \
    CONFIG.PCW_MIO_47_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_47_PULLUP {enabled} \
    CONFIG.PCW_MIO_47_SLEW {slow} \
    CONFIG.PCW_MIO_48_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_48_PULLUP {enabled} \
    CONFIG.PCW_MIO_48_SLEW {slow} \
    CONFIG.PCW_MIO_49_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_49_PULLUP {enabled} \
    CONFIG.PCW_MIO_49_SLEW {slow} \
    CONFIG.PCW_MIO_4_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_4_SLEW {slow} \
    CONFIG.PCW_MIO_50_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_50_PULLUP {enabled} \
    CONFIG.PCW_MIO_50_SLEW {slow} \
    CONFIG.PCW_MIO_51_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_51_PULLUP {enabled} \
    CONFIG.PCW_MIO_51_SLEW {slow} \
    CONFIG.PCW_MIO_52_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_52_PULLUP {enabled} \
    CONFIG.PCW_MIO_52_SLEW {slow} \
    CONFIG.PCW_MIO_53_IOTYPE {LVCMOS 1.8V} \
    CONFIG.PCW_MIO_53_PULLUP {enabled} \
    CONFIG.PCW_MIO_53_SLEW {slow} \
    CONFIG.PCW_MIO_5_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_5_SLEW {slow} \
    CONFIG.PCW_MIO_6_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_6_SLEW {slow} \
    CONFIG.PCW_MIO_7_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_7_SLEW {slow} \
    CONFIG.PCW_MIO_8_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_8_SLEW {slow} \
    CONFIG.PCW_MIO_9_IOTYPE {LVCMOS 3.3V} \
    CONFIG.PCW_MIO_9_PULLUP {enabled} \
    CONFIG.PCW_MIO_9_SLEW {slow} \
    CONFIG.PCW_MIO_PRIMITIVE {54} \
    CONFIG.PCW_MIO_TREE_PERIPHERALS {GPIO#Quad SPI Flash#Quad SPI Flash#Quad SPI Flash#Quad SPI Flash#Quad SPI Flash#Quad SPI Flash#GPIO#Quad SPI Flash#ENET Reset#GPIO#GPIO#GPIO#GPIO#UART 0#UART 0#Enet\
0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#Enet 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#USB 0#SD 0#SD 0#SD 0#SD 0#SD 0#SD 0#USB Reset#SD 0#GPIO#GPIO#GPIO#GPIO#Enet\
0#Enet 0} \
    CONFIG.PCW_MIO_TREE_SIGNALS {gpio[0]#qspi0_ss_b#qspi0_io[0]#qspi0_io[1]#qspi0_io[2]#qspi0_io[3]/HOLD_B#qspi0_sclk#gpio[7]#qspi_fbclk#reset#gpio[10]#gpio[11]#gpio[12]#gpio[13]#rx#tx#tx_clk#txd[0]#txd[1]#txd[2]#txd[3]#tx_ctl#rx_clk#rxd[0]#rxd[1]#rxd[2]#rxd[3]#rx_ctl#data[4]#dir#stp#nxt#data[0]#data[1]#data[2]#data[3]#clk#data[5]#data[6]#data[7]#clk#cmd#data[0]#data[1]#data[2]#data[3]#reset#cd#gpio[48]#gpio[49]#gpio[50]#gpio[51]#mdc#mdio}\
\
    CONFIG.PCW_M_AXI_GP0_ENABLE_STATIC_REMAP {0} \
    CONFIG.PCW_M_AXI_GP0_ID_WIDTH {12} \
    CONFIG.PCW_M_AXI_GP0_SUPPORT_NARROW_BURST {0} \
    CONFIG.PCW_M_AXI_GP0_THREAD_ID_WIDTH {12} \
    CONFIG.PCW_NAND_CYCLES_T_AR {1} \
    CONFIG.PCW_NAND_CYCLES_T_CLR {1} \
    CONFIG.PCW_NAND_CYCLES_T_RC {11} \
    CONFIG.PCW_NAND_CYCLES_T_REA {1} \
    CONFIG.PCW_NAND_CYCLES_T_RR {1} \
    CONFIG.PCW_NAND_CYCLES_T_WC {11} \
    CONFIG.PCW_NAND_CYCLES_T_WP {1} \
    CONFIG.PCW_NOR_CS0_T_CEOE {1} \
    CONFIG.PCW_NOR_CS0_T_PC {1} \
    CONFIG.PCW_NOR_CS0_T_RC {11} \
    CONFIG.PCW_NOR_CS0_T_TR {1} \
    CONFIG.PCW_NOR_CS0_T_WC {11} \
    CONFIG.PCW_NOR_CS0_T_WP {1} \
    CONFIG.PCW_NOR_CS0_WE_TIME {0} \
    CONFIG.PCW_NOR_CS1_T_CEOE {1} \
    CONFIG.PCW_NOR_CS1_T_PC {1} \
    CONFIG.PCW_NOR_CS1_T_RC {11} \
    CONFIG.PCW_NOR_CS1_T_TR {1} \
    CONFIG.PCW_NOR_CS1_T_WC {11} \
    CONFIG.PCW_NOR_CS1_T_WP {1} \
    CONFIG.PCW_NOR_CS1_WE_TIME {0} \
    CONFIG.PCW_NOR_SRAM_CS0_T_CEOE {1} \
    CONFIG.PCW_NOR_SRAM_CS0_T_PC {1} \
    CONFIG.PCW_NOR_SRAM_CS0_T_RC {11} \
    CONFIG.PCW_NOR_SRAM_CS0_T_TR {1} \
    CONFIG.PCW_NOR_SRAM_CS0_T_WC {11} \
    CONFIG.PCW_NOR_SRAM_CS0_T_WP {1} \
    CONFIG.PCW_NOR_SRAM_CS0_WE_TIME {0} \
    CONFIG.PCW_NOR_SRAM_CS1_T_CEOE {1} \
    CONFIG.PCW_NOR_SRAM_CS1_T_PC {1} \
    CONFIG.PCW_NOR_SRAM_CS1_T_RC {11} \
    CONFIG.PCW_NOR_SRAM_CS1_T_TR {1} \
    CONFIG.PCW_NOR_SRAM_CS1_T_WC {11} \
    CONFIG.PCW_NOR_SRAM_CS1_T_WP {1} \
    CONFIG.PCW_NOR_SRAM_CS1_WE_TIME {0} \
    CONFIG.PCW_OVERRIDE_BASIC_CLOCK {0} \
    CONFIG.PCW_PACKAGE_DDR_BOARD_DELAY0 {0.223} \
    CONFIG.PCW_PACKAGE_DDR_BOARD_DELAY1 {0.212} \
    CONFIG.PCW_PACKAGE_DDR_BOARD_DELAY2 {0.085} \
    CONFIG.PCW_PACKAGE_DDR_BOARD_DELAY3 {0.092} \
    CONFIG.PCW_PACKAGE_DDR_DQS_TO_CLK_DELAY_0 {0.040} \
    CONFIG.PCW_PACKAGE_DDR_DQS_TO_CLK_DELAY_1 {0.058} \
    CONFIG.PCW_PACKAGE_DDR_DQS_TO_CLK_DELAY_2 {-0.009} \
    CONFIG.PCW_PACKAGE_DDR_DQS_TO_CLK_DELAY_3 {-0.033} \
    CONFIG.PCW_PACKAGE_NAME {clg400} \
    CONFIG.PCW_PCAP_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_PCAP_PERIPHERAL_FREQMHZ {200} \
    CONFIG.PCW_PERIPHERAL_BOARD_PRESET {None} \
    CONFIG.PCW_PLL_BYPASSMODE_ENABLE {0} \
    CONFIG.PCW_PRESET_BANK0_VOLTAGE {LVCMOS 3.3V} \
    CONFIG.PCW_PRESET_BANK1_VOLTAGE {LVCMOS 1.8V} \
    CONFIG.PCW_PS7_SI_REV {PRODUCTION} \
    CONFIG.PCW_QSPI_GRP_FBCLK_ENABLE {1} \
    CONFIG.PCW_QSPI_GRP_FBCLK_IO {MIO 8} \
    CONFIG.PCW_QSPI_GRP_IO1_ENABLE {0} \
    CONFIG.PCW_QSPI_GRP_SINGLE_SS_ENABLE {1} \
    CONFIG.PCW_QSPI_GRP_SINGLE_SS_IO {MIO 1 .. 6} \
    CONFIG.PCW_QSPI_GRP_SS1_ENABLE {0} \
    CONFIG.PCW_QSPI_INTERNAL_HIGHADDRESS {0xFCFFFFFF} \
    CONFIG.PCW_QSPI_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_QSPI_PERIPHERAL_ENABLE {1} \
    CONFIG.PCW_QSPI_PERIPHERAL_FREQMHZ {200} \
    CONFIG.PCW_QSPI_QSPI_IO {MIO 1 .. 6} \
    CONFIG.PCW_SD0_GRP_CD_ENABLE {1} \
    CONFIG.PCW_SD0_GRP_CD_IO {MIO 47} \
    CONFIG.PCW_SD0_GRP_POW_ENABLE {0} \
    CONFIG.PCW_SD0_GRP_WP_ENABLE {0} \
    CONFIG.PCW_SD0_PERIPHERAL_ENABLE {1} \
    CONFIG.PCW_SD0_SD0_IO {MIO 40 .. 45} \
    CONFIG.PCW_SD1_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_SDIO0_BASEADDR {0xE0100000} \
    CONFIG.PCW_SDIO0_HIGHADDR {0xE0100FFF} \
    CONFIG.PCW_SDIO_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_SDIO_PERIPHERAL_FREQMHZ {50} \
    CONFIG.PCW_SDIO_PERIPHERAL_VALID {1} \
    CONFIG.PCW_SINGLE_QSPI_DATA_MODE {x4} \
    CONFIG.PCW_SMC_CYCLE_T0 {NA} \
    CONFIG.PCW_SMC_CYCLE_T1 {NA} \
    CONFIG.PCW_SMC_CYCLE_T2 {NA} \
    CONFIG.PCW_SMC_CYCLE_T3 {NA} \
    CONFIG.PCW_SMC_CYCLE_T4 {NA} \
    CONFIG.PCW_SMC_CYCLE_T5 {NA} \
    CONFIG.PCW_SMC_CYCLE_T6 {NA} \
    CONFIG.PCW_SMC_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_SMC_PERIPHERAL_VALID {0} \
    CONFIG.PCW_SPI0_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_SPI1_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_SPI_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_SPI_PERIPHERAL_VALID {0} \
    CONFIG.PCW_TPIU_PERIPHERAL_CLKSRC {External} \
    CONFIG.PCW_TTC0_CLK0_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_TTC0_CLK0_PERIPHERAL_DIVISOR0 {1} \
    CONFIG.PCW_TTC0_CLK1_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_TTC0_CLK1_PERIPHERAL_DIVISOR0 {1} \
    CONFIG.PCW_TTC0_CLK2_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_TTC0_CLK2_PERIPHERAL_DIVISOR0 {1} \
    CONFIG.PCW_TTC1_CLK0_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_TTC1_CLK0_PERIPHERAL_DIVISOR0 {1} \
    CONFIG.PCW_TTC1_CLK1_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_TTC1_CLK1_PERIPHERAL_DIVISOR0 {1} \
    CONFIG.PCW_TTC1_CLK2_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_TTC1_CLK2_PERIPHERAL_DIVISOR0 {1} \
    CONFIG.PCW_UART0_BASEADDR {0xE0000000} \
    CONFIG.PCW_UART0_BAUD_RATE {115200} \
    CONFIG.PCW_UART0_GRP_FULL_ENABLE {0} \
    CONFIG.PCW_UART0_HIGHADDR {0xE0000FFF} \
    CONFIG.PCW_UART0_PERIPHERAL_ENABLE {1} \
    CONFIG.PCW_UART0_UART0_IO {MIO 14 .. 15} \
    CONFIG.PCW_UART1_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_UART_PERIPHERAL_CLKSRC {IO PLL} \
    CONFIG.PCW_UART_PERIPHERAL_FREQMHZ {100} \
    CONFIG.PCW_UART_PERIPHERAL_VALID {1} \
    CONFIG.PCW_UIPARAM_ACT_DDR_FREQ_MHZ {525.000000} \
    CONFIG.PCW_UIPARAM_DDR_ADV_ENABLE {0} \
    CONFIG.PCW_UIPARAM_DDR_AL {0} \
    CONFIG.PCW_UIPARAM_DDR_BL {8} \
    CONFIG.PCW_UIPARAM_DDR_BOARD_DELAY0 {0.223} \
    CONFIG.PCW_UIPARAM_DDR_BOARD_DELAY1 {0.212} \
    CONFIG.PCW_UIPARAM_DDR_BOARD_DELAY2 {0.085} \
    CONFIG.PCW_UIPARAM_DDR_BOARD_DELAY3 {0.092} \
    CONFIG.PCW_UIPARAM_DDR_BUS_WIDTH {16 Bit} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_0_LENGTH_MM {25.8} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_0_PACKAGE_LENGTH {80.4535} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_0_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_1_LENGTH_MM {25.8} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_1_PACKAGE_LENGTH {80.4535} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_1_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_2_LENGTH_MM {0} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_2_PACKAGE_LENGTH {80.4535} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_2_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_3_LENGTH_MM {0} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_3_PACKAGE_LENGTH {80.4535} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_3_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_CLOCK_STOP_EN {0} \
    CONFIG.PCW_UIPARAM_DDR_DQS_0_LENGTH_MM {15.6} \
    CONFIG.PCW_UIPARAM_DDR_DQS_0_PACKAGE_LENGTH {105.056} \
    CONFIG.PCW_UIPARAM_DDR_DQS_0_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQS_1_LENGTH_MM {18.8} \
    CONFIG.PCW_UIPARAM_DDR_DQS_1_PACKAGE_LENGTH {66.904} \
    CONFIG.PCW_UIPARAM_DDR_DQS_1_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQS_2_LENGTH_MM {0} \
    CONFIG.PCW_UIPARAM_DDR_DQS_2_PACKAGE_LENGTH {89.1715} \
    CONFIG.PCW_UIPARAM_DDR_DQS_2_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQS_3_LENGTH_MM {0} \
    CONFIG.PCW_UIPARAM_DDR_DQS_3_PACKAGE_LENGTH {113.63} \
    CONFIG.PCW_UIPARAM_DDR_DQS_3_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQS_TO_CLK_DELAY_0 {0.040} \
    CONFIG.PCW_UIPARAM_DDR_DQS_TO_CLK_DELAY_1 {0.058} \
    CONFIG.PCW_UIPARAM_DDR_DQS_TO_CLK_DELAY_2 {-0.009} \
    CONFIG.PCW_UIPARAM_DDR_DQS_TO_CLK_DELAY_3 {-0.033} \
    CONFIG.PCW_UIPARAM_DDR_DQ_0_LENGTH_MM {16.5} \
    CONFIG.PCW_UIPARAM_DDR_DQ_0_PACKAGE_LENGTH {98.503} \
    CONFIG.PCW_UIPARAM_DDR_DQ_0_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQ_1_LENGTH_MM {18} \
    CONFIG.PCW_UIPARAM_DDR_DQ_1_PACKAGE_LENGTH {68.5855} \
    CONFIG.PCW_UIPARAM_DDR_DQ_1_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQ_2_LENGTH_MM {0} \
    CONFIG.PCW_UIPARAM_DDR_DQ_2_PACKAGE_LENGTH {90.295} \
    CONFIG.PCW_UIPARAM_DDR_DQ_2_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_DQ_3_LENGTH_MM {0} \
    CONFIG.PCW_UIPARAM_DDR_DQ_3_PACKAGE_LENGTH {103.977} \
    CONFIG.PCW_UIPARAM_DDR_DQ_3_PROPOGATION_DELAY {160} \
    CONFIG.PCW_UIPARAM_DDR_ECC {Disabled} \
    CONFIG.PCW_UIPARAM_DDR_ENABLE {1} \
    CONFIG.PCW_UIPARAM_DDR_FREQ_MHZ {525} \
    CONFIG.PCW_UIPARAM_DDR_HIGH_TEMP {Normal (0-85)} \
    CONFIG.PCW_UIPARAM_DDR_MEMORY_TYPE {DDR 3} \
    CONFIG.PCW_UIPARAM_DDR_PARTNO {MT41J256M16 RE-125} \
    CONFIG.PCW_UIPARAM_DDR_TRAIN_DATA_EYE {1} \
    CONFIG.PCW_UIPARAM_DDR_TRAIN_READ_GATE {1} \
    CONFIG.PCW_UIPARAM_DDR_TRAIN_WRITE_LEVEL {1} \
    CONFIG.PCW_UIPARAM_DDR_USE_INTERNAL_VREF {0} \
    CONFIG.PCW_UIPARAM_GENERATE_SUMMARY {NA} \
    CONFIG.PCW_USB0_BASEADDR {0xE0102000} \
    CONFIG.PCW_USB0_HIGHADDR {0xE0102fff} \
    CONFIG.PCW_USB0_PERIPHERAL_ENABLE {1} \
    CONFIG.PCW_USB0_RESET_ENABLE {1} \
    CONFIG.PCW_USB0_RESET_IO {MIO 46} \
    CONFIG.PCW_USB0_USB0_IO {MIO 28 .. 39} \
    CONFIG.PCW_USB1_PERIPHERAL_ENABLE {0} \
    CONFIG.PCW_USB_RESET_ENABLE {1} \
    CONFIG.PCW_USB_RESET_POLARITY {Active Low} \
    CONFIG.PCW_USB_RESET_SELECT {Share reset pin} \
    CONFIG.PCW_USE_AXI_FABRIC_IDLE {0} \
    CONFIG.PCW_USE_AXI_NONSECURE {0} \
    CONFIG.PCW_USE_CORESIGHT {0} \
    CONFIG.PCW_USE_CROSS_TRIGGER {0} \
    CONFIG.PCW_USE_CR_FABRIC {1} \
    CONFIG.PCW_USE_DDR_BYPASS {0} \
    CONFIG.PCW_USE_DEBUG {0} \
    CONFIG.PCW_USE_DMA0 {0} \
    CONFIG.PCW_USE_DMA1 {0} \
    CONFIG.PCW_USE_DMA2 {0} \
    CONFIG.PCW_USE_DMA3 {0} \
    CONFIG.PCW_USE_EXPANDED_IOP {0} \
    CONFIG.PCW_USE_FABRIC_INTERRUPT {0} \
    CONFIG.PCW_USE_HIGH_OCM {0} \
    CONFIG.PCW_USE_M_AXI_GP0 {1} \
    CONFIG.PCW_USE_M_AXI_GP1 {0} \
    CONFIG.PCW_USE_PROC_EVENT_BUS {0} \
    CONFIG.PCW_USE_PS_SLCR_REGISTERS {0} \
    CONFIG.PCW_USE_S_AXI_ACP {0} \
    CONFIG.PCW_USE_S_AXI_GP0 {0} \
    CONFIG.PCW_USE_S_AXI_GP1 {0} \
    CONFIG.PCW_USE_S_AXI_HP0 {0} \
    CONFIG.PCW_USE_S_AXI_HP1 {0} \
    CONFIG.PCW_USE_S_AXI_HP2 {0} \
    CONFIG.PCW_USE_S_AXI_HP3 {0} \
    CONFIG.PCW_USE_TRACE {0} \
    CONFIG.PCW_VALUE_SILVERSION {3} \
    CONFIG.PCW_WDT_PERIPHERAL_CLKSRC {CPU_1X} \
    CONFIG.PCW_WDT_PERIPHERAL_DIVISOR0 {1} \
  ] $processing_system7_0


  # Create instance: rst_ps7_0_100M, and set properties
  set rst_ps7_0_100M [ create_bd_cell -type ip -vlnv xilinx.com:ip:proc_sys_reset:5.0 rst_ps7_0_100M ]

  # Create instance: adc_0
  create_hier_cell_adc_0 [current_bd_instance .] adc_0

  # Create instance: axi_smc, and set properties
  set axi_smc [ create_bd_cell -type ip -vlnv xilinx.com:ip:smartconnect:1.0 axi_smc ]
  set_property -dict [list \
    CONFIG.NUM_MI {32} \
    CONFIG.NUM_SI {1} \
  ] $axi_smc


  # Create instance: adc_1
  create_hier_cell_adc_1 [current_bd_instance .] adc_1

  # Create instance: adc_2
  create_hier_cell_adc_2 [current_bd_instance .] adc_2

  # Create instance: adc_3
  create_hier_cell_adc_3 [current_bd_instance .] adc_3

  # Create instance: adc_4
  create_hier_cell_adc_4 [current_bd_instance .] adc_4

  # Create instance: adc_5
  create_hier_cell_adc_5 [current_bd_instance .] adc_5

  # Create instance: adc_6
  create_hier_cell_adc_6 [current_bd_instance .] adc_6

  # Create instance: adc_7
  create_hier_cell_adc_7 [current_bd_instance .] adc_7

  # Create instance: adc_8
  create_hier_cell_adc_8 [current_bd_instance .] adc_8

  # Create instance: adc_9
  create_hier_cell_adc_9 [current_bd_instance .] adc_9

  # Create instance: adc_10
  create_hier_cell_adc_10 [current_bd_instance .] adc_10

  # Create instance: adc_11
  create_hier_cell_adc_11 [current_bd_instance .] adc_11

  # Create instance: adc_12
  create_hier_cell_adc_12 [current_bd_instance .] adc_12

  # Create instance: adc_13
  create_hier_cell_adc_13 [current_bd_instance .] adc_13

  # Create instance: adc_14
  create_hier_cell_adc_14 [current_bd_instance .] adc_14

  # Create instance: adc_15
  create_hier_cell_adc_15 [current_bd_instance .] adc_15

  # Create instance: ADAQ4001
  create_hier_cell_ADAQ4001 [current_bd_instance .] ADAQ4001

  # Create interface connections
  connect_bd_intf_net -intf_net axi_smc_M00_AXI [get_bd_intf_pins axi_smc/M00_AXI] [get_bd_intf_pins adc_0/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M01_AXI [get_bd_intf_pins axi_smc/M01_AXI] [get_bd_intf_pins adc_0/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M02_AXI [get_bd_intf_pins axi_smc/M02_AXI] [get_bd_intf_pins adc_1/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M03_AXI [get_bd_intf_pins axi_smc/M03_AXI] [get_bd_intf_pins adc_1/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M04_AXI [get_bd_intf_pins axi_smc/M04_AXI] [get_bd_intf_pins adc_2/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M05_AXI [get_bd_intf_pins axi_smc/M05_AXI] [get_bd_intf_pins adc_2/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M06_AXI [get_bd_intf_pins axi_smc/M06_AXI] [get_bd_intf_pins adc_3/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M07_AXI [get_bd_intf_pins axi_smc/M07_AXI] [get_bd_intf_pins adc_3/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M08_AXI [get_bd_intf_pins axi_smc/M08_AXI] [get_bd_intf_pins adc_4/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M09_AXI [get_bd_intf_pins axi_smc/M09_AXI] [get_bd_intf_pins adc_4/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M10_AXI [get_bd_intf_pins axi_smc/M10_AXI] [get_bd_intf_pins adc_5/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M11_AXI [get_bd_intf_pins axi_smc/M11_AXI] [get_bd_intf_pins adc_5/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M12_AXI [get_bd_intf_pins axi_smc/M12_AXI] [get_bd_intf_pins adc_6/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M13_AXI [get_bd_intf_pins axi_smc/M13_AXI] [get_bd_intf_pins adc_6/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M14_AXI [get_bd_intf_pins axi_smc/M14_AXI] [get_bd_intf_pins adc_7/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M15_AXI [get_bd_intf_pins axi_smc/M15_AXI] [get_bd_intf_pins adc_7/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M16_AXI [get_bd_intf_pins axi_smc/M16_AXI] [get_bd_intf_pins adc_8/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M17_AXI [get_bd_intf_pins axi_smc/M17_AXI] [get_bd_intf_pins adc_8/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M18_AXI [get_bd_intf_pins axi_smc/M18_AXI] [get_bd_intf_pins adc_9/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M19_AXI [get_bd_intf_pins axi_smc/M19_AXI] [get_bd_intf_pins adc_9/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M20_AXI [get_bd_intf_pins axi_smc/M20_AXI] [get_bd_intf_pins adc_10/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M21_AXI [get_bd_intf_pins axi_smc/M21_AXI] [get_bd_intf_pins adc_10/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M22_AXI [get_bd_intf_pins axi_smc/M22_AXI] [get_bd_intf_pins adc_11/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M23_AXI [get_bd_intf_pins axi_smc/M23_AXI] [get_bd_intf_pins adc_11/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M24_AXI [get_bd_intf_pins axi_smc/M24_AXI] [get_bd_intf_pins adc_12/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M25_AXI [get_bd_intf_pins axi_smc/M25_AXI] [get_bd_intf_pins adc_12/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M26_AXI [get_bd_intf_pins axi_smc/M26_AXI] [get_bd_intf_pins adc_13/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M27_AXI [get_bd_intf_pins axi_smc/M27_AXI] [get_bd_intf_pins adc_13/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M28_AXI [get_bd_intf_pins axi_smc/M28_AXI] [get_bd_intf_pins adc_14/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M29_AXI [get_bd_intf_pins axi_smc/M29_AXI] [get_bd_intf_pins adc_14/S_AXI1]
  connect_bd_intf_net -intf_net axi_smc_M30_AXI [get_bd_intf_pins axi_smc/M30_AXI] [get_bd_intf_pins adc_15/S_AXI]
  connect_bd_intf_net -intf_net axi_smc_M31_AXI [get_bd_intf_pins axi_smc/M31_AXI] [get_bd_intf_pins adc_15/S_AXI1]
  connect_bd_intf_net -intf_net processing_system7_0_DDR [get_bd_intf_ports DDR] [get_bd_intf_pins processing_system7_0/DDR]
  connect_bd_intf_net -intf_net processing_system7_0_FIXED_IO [get_bd_intf_ports FIXED_IO] [get_bd_intf_pins processing_system7_0/FIXED_IO]
  connect_bd_intf_net -intf_net processing_system7_0_M_AXI_GP0 [get_bd_intf_pins processing_system7_0/M_AXI_GP0] [get_bd_intf_pins axi_smc/S00_AXI]

  # Create port connections
  connect_bd_net -net ADAQ4001_0_cnv [get_bd_pins ADAQ4001/cnv] [get_bd_ports cnv]
  connect_bd_net -net ADAQ4001_0_data_out [get_bd_pins ADAQ4001/data_out4] [get_bd_pins adc_0/data_in]
  connect_bd_net -net ADAQ4001_0_data_ready [get_bd_pins ADAQ4001/data_ready4] [get_bd_pins adc_0/din_rdy]
  connect_bd_net -net ADAQ4001_0_sck [get_bd_pins ADAQ4001/sck] [get_bd_ports sck]
  connect_bd_net -net ADAQ4001_10_data_out [get_bd_pins ADAQ4001/data_out3] [get_bd_pins adc_10/data_in]
  connect_bd_net -net ADAQ4001_10_data_ready [get_bd_pins ADAQ4001/data_ready3] [get_bd_pins adc_10/din_rdy]
  connect_bd_net -net ADAQ4001_11_data_out [get_bd_pins ADAQ4001/data_out5] [get_bd_pins adc_11/data_in]
  connect_bd_net -net ADAQ4001_11_data_ready [get_bd_pins ADAQ4001/data_ready5] [get_bd_pins adc_11/din_rdy]
  connect_bd_net -net ADAQ4001_12_data_out [get_bd_pins ADAQ4001/data_out6] [get_bd_pins adc_12/data_in]
  connect_bd_net -net ADAQ4001_12_data_ready [get_bd_pins ADAQ4001/data_ready6] [get_bd_pins adc_12/din_rdy]
  connect_bd_net -net ADAQ4001_13_data_out [get_bd_pins ADAQ4001/data_out7] [get_bd_pins adc_13/data_in]
  connect_bd_net -net ADAQ4001_13_data_ready [get_bd_pins ADAQ4001/data_ready7] [get_bd_pins adc_13/din_rdy]
  connect_bd_net -net ADAQ4001_14_data_out [get_bd_pins ADAQ4001/data_out9] [get_bd_pins adc_14/data_in]
  connect_bd_net -net ADAQ4001_14_data_ready [get_bd_pins ADAQ4001/data_ready9] [get_bd_pins adc_14/din_rdy]
  connect_bd_net -net ADAQ4001_15_data_out [get_bd_pins ADAQ4001/data_out10] [get_bd_pins adc_15/data_in]
  connect_bd_net -net ADAQ4001_15_data_ready [get_bd_pins ADAQ4001/data_ready10] [get_bd_pins adc_15/din_rdy]
  connect_bd_net -net ADAQ4001_2_data_out [get_bd_pins ADAQ4001/data_out13] [get_bd_pins adc_2/data_in]
  connect_bd_net -net ADAQ4001_2_data_ready [get_bd_pins ADAQ4001/data_ready14] [get_bd_pins adc_2/din_rdy]
  connect_bd_net -net ADAQ4001_3_data_out [get_bd_pins ADAQ4001/data_out8] [get_bd_pins adc_3/data_in]
  connect_bd_net -net ADAQ4001_3_data_ready [get_bd_pins ADAQ4001/data_ready8] [get_bd_pins adc_3/din_rdy]
  connect_bd_net -net ADAQ4001_4_data_ready [get_bd_pins ADAQ4001/data_ready11] [get_bd_pins adc_4/din_rdy]
  connect_bd_net -net ADAQ4001_5_data_out [get_bd_pins ADAQ4001/data_out11] [get_bd_pins adc_5/data_in]
  connect_bd_net -net ADAQ4001_5_data_ready [get_bd_pins ADAQ4001/data_ready12] [get_bd_pins adc_5/din_rdy]
  connect_bd_net -net ADAQ4001_6_data_out [get_bd_pins ADAQ4001/data_out12] [get_bd_pins adc_6/data_in]
  connect_bd_net -net ADAQ4001_6_data_ready [get_bd_pins ADAQ4001/data_ready13] [get_bd_pins adc_6/din_rdy]
  connect_bd_net -net ADAQ4001_7_data_out [get_bd_pins ADAQ4001/data_out] [get_bd_pins adc_7/data_in]
  connect_bd_net -net ADAQ4001_7_data_ready [get_bd_pins ADAQ4001/data_ready] [get_bd_pins adc_7/din_rdy]
  connect_bd_net -net ADAQ4001_8_data_out [get_bd_pins ADAQ4001/data_out1] [get_bd_pins adc_8/data_in]
  connect_bd_net -net ADAQ4001_8_data_ready [get_bd_pins ADAQ4001/data_ready1] [get_bd_pins adc_8/din_rdy]
  connect_bd_net -net ADAQ4001_9_data_out [get_bd_pins ADAQ4001/data_out2] [get_bd_pins adc_9/data_in]
  connect_bd_net -net ADAQ4001_9_data_ready [get_bd_pins ADAQ4001/data_ready2] [get_bd_pins adc_9/din_rdy]
  connect_bd_net -net data_in_1 [get_bd_pins ADAQ4001/cnv1] [get_bd_pins adc_4/data_in]
  connect_bd_net -net data_in_2 [get_bd_pins ADAQ4001/cnv2] [get_bd_pins adc_1/data_in]
  connect_bd_net -net din_rdy_1 [get_bd_pins ADAQ4001/data_ready15] [get_bd_pins adc_1/din_rdy]
  connect_bd_net -net processing_system7_0_FCLK_CLK0 [get_bd_pins processing_system7_0/FCLK_CLK0] [get_bd_pins processing_system7_0/M_AXI_GP0_ACLK] [get_bd_pins rst_ps7_0_100M/slowest_sync_clk] [get_bd_pins adc_0/s_axi_aclk] [get_bd_pins axi_smc/aclk] [get_bd_pins adc_1/s_axi_aclk] [get_bd_pins adc_2/s_axi_aclk] [get_bd_pins adc_3/s_axi_aclk] [get_bd_pins adc_4/s_axi_aclk] [get_bd_pins adc_5/s_axi_aclk] [get_bd_pins adc_6/s_axi_aclk] [get_bd_pins adc_7/s_axi_aclk] [get_bd_pins adc_8/s_axi_aclk] [get_bd_pins adc_9/s_axi_aclk] [get_bd_pins adc_10/s_axi_aclk] [get_bd_pins adc_11/s_axi_aclk] [get_bd_pins adc_12/s_axi_aclk] [get_bd_pins adc_13/s_axi_aclk] [get_bd_pins adc_14/s_axi_aclk] [get_bd_pins adc_15/s_axi_aclk] [get_bd_pins ADAQ4001/clk]
  connect_bd_net -net processing_system7_0_FCLK_RESET0_N [get_bd_pins processing_system7_0/FCLK_RESET0_N] [get_bd_pins rst_ps7_0_100M/ext_reset_in]
  connect_bd_net -net rst_ps7_0_100M_peripheral_aresetn [get_bd_pins rst_ps7_0_100M/peripheral_aresetn] [get_bd_pins adc_0/s_axi_aresetn] [get_bd_pins axi_smc/aresetn] [get_bd_pins adc_1/s_axi_aresetn] [get_bd_pins adc_2/s_axi_aresetn] [get_bd_pins adc_3/s_axi_aresetn] [get_bd_pins adc_4/s_axi_aresetn] [get_bd_pins adc_5/s_axi_aresetn] [get_bd_pins adc_6/s_axi_aresetn] [get_bd_pins adc_7/s_axi_aresetn] [get_bd_pins adc_8/s_axi_aresetn] [get_bd_pins adc_9/s_axi_aresetn] [get_bd_pins adc_10/s_axi_aresetn] [get_bd_pins adc_11/s_axi_aresetn] [get_bd_pins adc_12/s_axi_aresetn] [get_bd_pins adc_13/s_axi_aresetn] [get_bd_pins adc_14/s_axi_aresetn] [get_bd_pins adc_15/s_axi_aresetn] [get_bd_pins ADAQ4001/reset_n]
  connect_bd_net -net sdo_0_1 [get_bd_ports sdo_0] [get_bd_pins ADAQ4001/sdo_0]
  connect_bd_net -net sdo_10_1 [get_bd_ports sdo_10] [get_bd_pins ADAQ4001/sdo_10]
  connect_bd_net -net sdo_11_1 [get_bd_ports sdo_11] [get_bd_pins ADAQ4001/sdo_11]
  connect_bd_net -net sdo_12_1 [get_bd_ports sdo_12] [get_bd_pins ADAQ4001/sdo_12]
  connect_bd_net -net sdo_13_1 [get_bd_ports sdo_13] [get_bd_pins ADAQ4001/sdo_13]
  connect_bd_net -net sdo_14_1 [get_bd_ports sdo_14] [get_bd_pins ADAQ4001/sdo_14]
  connect_bd_net -net sdo_15_1 [get_bd_ports sdo_15] [get_bd_pins ADAQ4001/sdo_15]
  connect_bd_net -net sdo_1_1 [get_bd_ports sdo_1] [get_bd_pins ADAQ4001/sdo_1]
  connect_bd_net -net sdo_2_1 [get_bd_ports sdo_2] [get_bd_pins ADAQ4001/sdo_2]
  connect_bd_net -net sdo_3_1 [get_bd_ports sdo_3] [get_bd_pins ADAQ4001/sdo_3]
  connect_bd_net -net sdo_4_1 [get_bd_ports sdo_4] [get_bd_pins ADAQ4001/sdo_4]
  connect_bd_net -net sdo_5_1 [get_bd_ports sdo_5] [get_bd_pins ADAQ4001/sdo_5]
  connect_bd_net -net sdo_6_1 [get_bd_ports sdo_6] [get_bd_pins ADAQ4001/sdo_6]
  connect_bd_net -net sdo_7_1 [get_bd_ports sdo_7] [get_bd_pins ADAQ4001/sdo_7]
  connect_bd_net -net sdo_8_1 [get_bd_ports sdo_8] [get_bd_pins ADAQ4001/sdo_8]
  connect_bd_net -net sdo_9_1 [get_bd_ports sdo_9] [get_bd_pins ADAQ4001/sdo_9]

  # Create address segments
  assign_bd_address -offset 0x7AA00000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_0/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA10000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_1 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_1/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA20000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_2 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_2/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA30000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_3 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_3/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA40000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_4 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_4/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA50000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_5 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_5/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA60000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_6 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_6/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA70000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_7 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_7/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA80000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_8 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_8/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AA90000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_9 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_9/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AAA0000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_10 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_10/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AAB0000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_11 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_11/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AAC0000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_12 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_12/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AAD0000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_13 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_13/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AAE0000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_14 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_14/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x7AAF0000 -range 0x00010000 -with_name SEG_anchor_0_S_AXI_mem_15 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_15/anchor_0/S_AXI/S_AXI_mem] -force
  assign_bd_address -offset 0x43C00000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_0/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C10000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_1 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_1/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C20000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_2 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_2/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C30000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_3 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_3/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C40000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_4 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_4/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C50000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_5 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_5/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C60000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_6 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_6/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C70000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_7 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_7/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C80000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_8 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_8/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43C90000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_9 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_9/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43CA0000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_10 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_10/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43CB0000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_11 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_11/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43CC0000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_12 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_12/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43CD0000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_13 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_13/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43CE0000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_14 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_14/filter_ctrl_0/S_AXI/S_AXI_reg] -force
  assign_bd_address -offset 0x43CF0000 -range 0x00010000 -with_name SEG_filter_ctrl_0_S_AXI_reg_15 -target_address_space [get_bd_addr_spaces processing_system7_0/Data] [get_bd_addr_segs adc_15/filter_ctrl_0/S_AXI/S_AXI_reg] -force


  # Restore current instance
  current_bd_instance $oldCurInst

  validate_bd_design
  save_bd_design
}
# End of create_root_design()


##################################################################
# MAIN FLOW
##################################################################

create_root_design ""


