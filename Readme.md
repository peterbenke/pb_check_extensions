# TYPO3 Extension ``pb_check_extensions`` 

## Description

Checks, if there are updates available for installed extensions and sends an email


## What does it do?

This extension provides a scheduler job. This job checks, if there are updates available for installed extensions and sends an email.

* You can exclude extensions, which should be excluded from this check


## Administration

### Installation

Install this extension via composer
    
    composer req peterbenke/pb-check-extensions

### Configuration

1. Go to the scheduler and create a new scheduler job **pb_check_extensions** => "Check extensions for update"
2. Input values for the following fields: "Email subject", "Email address from", "Send email to addresses", "Exclude extensions" (if needed)

**Important**

To get this extension running, you also have to create a system scheduler job **extensionmanager** => "Update extension list".
Take care to run this scheduler job **before** the pb_check_extensions-job.
