<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use App\Enum\Permission;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class RoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            ChoiceField::new('permissions')
                ->setChoices(Permission::choices())
                ->allowMultipleChoices()
                ->renderExpanded(),
            AssociationField::new('users')
                ->hideOnForm()
                ->setTemplatePath('admin/field/user_count.html.twig'),
        ];
    }
}
