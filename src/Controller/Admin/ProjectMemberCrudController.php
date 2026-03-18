<?php

namespace App\Controller\Admin;

use App\Entity\ProjectMember;
use App\Enum\ProjectPermission;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class ProjectMemberCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProjectMember::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Project Member')
            ->setEntityLabelInPlural('Project Members');
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('project'),
            AssociationField::new('user'),
            ChoiceField::new('permissions')
                ->setChoices(ProjectPermission::choices())
                ->allowMultipleChoices()
                ->renderExpanded(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('project'))
            ->add(EntityFilter::new('user'));
    }
}
