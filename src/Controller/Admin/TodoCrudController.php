<?php

namespace App\Controller\Admin;

use App\Entity\Todo;
use App\Enum\TodoStatus;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;

class TodoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Todo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            TextareaField::new('description')->hideOnIndex(),
            ChoiceField::new('status')
                ->setChoices(TodoStatus::cases())
                ->renderAsBadges([
                    TodoStatus::TODO->value => 'secondary',
                    TodoStatus::IN_PROGRESS->value => 'warning',
                    TodoStatus::DONE->value => 'success',
                ]),
            AssociationField::new('project'),
            AssociationField::new('assignedTo', 'Assigned To'),
            NumberField::new('estimatedHours', 'Estimate (h)')
                ->setNumDecimals(1),
            NumberField::new('spentHours', 'Spent (h)')
                ->setNumDecimals(1),
            DateField::new('dueDate'),
            DateTimeField::new('createdAt')->hideOnForm(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('project'))
            ->add(EntityFilter::new('assignedTo'))
            ->add(ChoiceFilter::new('status')->setChoices(TodoStatus::cases()));
    }
}
