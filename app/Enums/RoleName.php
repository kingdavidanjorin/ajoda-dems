<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleName: string
{
    case Customer = 'Customer';
    case SalesOfficer = 'Sales Officer';
    case AccountsOfficer = 'Accounts Officer';
    case ProjectManager = 'Project Manager';
    case CustomerServiceOfficer = 'Customer Service Officer';
    case PropertyContentOfficer = 'Property and Content Officer';
    case DocumentOfficer = 'Document Officer';
    case SystemAdministrator = 'System Administrator';
    case ExecutiveManagement = 'Executive Management';

    public static function assignableStaffValues(): array
    {
        return array_values(
            array_map(
                static fn (self $role): string => $role->value,
                array_filter(
                    self::cases(),
                    static fn (self $role): bool => ! in_array(
                        $role,
                        [
                            self::Customer,
                            self::SystemAdministrator,
                        ],
                        true,
                    ),
                ),
            ),
        );
    }
}
