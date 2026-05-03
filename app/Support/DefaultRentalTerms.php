<?php

namespace App\Support;

final class DefaultRentalTerms
{
    public static function content(): string
    {
        return <<<'MARKDOWN'

# Rental Terms and Conditions

## 1. Rent Payment
Monthly rent is due on the date specified in your lease agreement.
Failure to pay on time will result in a late payment fee as specified
in your lease contract.

## 2. Late Payment Policy
A late fee will be applied to any payment not received by the due date.
The late fee amount is determined by your property owner and will be
clearly shown in your payment breakdown.

## 3. Property Care and Maintenance
Tenants are responsible for maintaining the unit in good condition.
Any damage beyond normal wear and tear will be charged to the tenant
and may be deducted from the security deposit.

## 4. Security Deposit
The security deposit will be held for the duration of the lease.
It will be returned within 30 days after move-out, minus any
deductions for damages or unpaid rent.

## 5. Prohibited Activities
- No illegal activities on the premises
- No unauthorized pets unless specified in lease
- No subletting without written permission from the owner
- No structural modifications to the unit

## 6. Utilities and Inclusions
Utilities included in the rent are specified in your lease agreement.
Tenant is responsible for all other utilities not mentioned.

## 7. Move-Out Policy
Tenant must give at least 30 days written notice before move-out.
The unit must be returned in the same condition as move-in.

## 8. Dispute Resolution
Any disputes arising from this tenancy will be resolved through
mutual agreement. If unresolved, parties may seek legal remedies
as provided by Philippine law.

## 9. Acknowledgment
By agreeing to these terms, you confirm that you have read,
understood, and agree to all conditions stated above.

MARKDOWN;
    }
}
