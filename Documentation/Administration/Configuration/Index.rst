.. ==================================================
.. FOR YOUR INFORMATION
.. --------------------------------------------------
.. -*- coding: utf-8 -*- with BOM.

.. include:: ../../Includes.txt


.. _administration-configuration:

Configuration
=============

#. Go to the scheduler and create a new scheduler job **pb_check_extensions** => "Check extensions for update"

#. Input values for the following fields: "Email subject", "Email address from", "Send email to addresses", "Exclude extensions" (if needed)


**Important**

To get this extension running, you also have to create a system scheduler job **extensionmanager** => "Update extension list".
Take care to run this scheduler job **before** the pb_check_extensions-job.