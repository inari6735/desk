<?php

namespace App\Form;

use App\Entity\TimeEntry;
use App\Entity\Todo;
use App\Enum\ActionType;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SessionEntryFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('todo', EntityType::class, [
                'class' => Todo::class,
                'choice_label' => fn (Todo $t) => sprintf('%s — %s', $t->getProject()?->getName() ?? '?', $t->getTitle()),
                'placeholder' => 'Select a task...',
                'query_builder' => fn (EntityRepository $er) => $er->createQueryBuilder('t')
                    ->join('t.project', 'p')
                    ->orderBy('p.name', 'ASC')
                    ->addOrderBy('t.title', 'ASC'),
                'constraints' => [new NotBlank()],
                'group_by' => fn (Todo $t) => $t->getProject()?->getName() ?? 'No project',
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
                'constraints' => [new NotBlank()],
            ])
            ->add('startTime', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'constraints' => [new NotBlank()],
            ])
            ->add('endTime', TimeType::class, [
                'widget' => 'single_text',
                'input' => 'datetime',
                'constraints' => [new NotBlank()],
            ])
            ->add('actionType', EnumType::class, [
                'class' => ActionType::class,
                'choice_label' => fn (ActionType $a) => $a->label(),
                'placeholder' => 'Select action type...',
                'required' => false,
            ])
            ->add('note', TextareaType::class, [
                'required' => false,
                'attr' => ['rows' => 2, 'placeholder' => 'What did you work on?'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TimeEntry::class,
            'constraints' => [
                new Callback(function (TimeEntry $entry, ExecutionContextInterface $context): void {
                    $now = new \DateTimeImmutable();

                    // Date cannot be in the future
                    if ($entry->getDate() > $now->setTime(23, 59, 59)) {
                        $context->buildViolation('Cannot log time for future dates.')
                            ->atPath('date')
                            ->addViolation();
                    }

                    // If today, end time cannot be in the future
                    if ($entry->getDate() && $entry->getEndTime()
                        && $entry->getDate()->format('Y-m-d') === $now->format('Y-m-d')
                    ) {
                        $endMinutes = (int) $entry->getEndTime()->format('H') * 60 + (int) $entry->getEndTime()->format('i');
                        $nowMinutes = (int) $now->format('H') * 60 + (int) $now->format('i');
                        if ($endMinutes > $nowMinutes) {
                            $context->buildViolation('End time cannot be in the future.')
                                ->atPath('endTime')
                                ->addViolation();
                        }
                    }

                    // End must be after start
                    if ($entry->getStartTime() && $entry->getEndTime()
                        && $entry->getEndTime() <= $entry->getStartTime()
                    ) {
                        $context->buildViolation('End time must be after start time.')
                            ->atPath('endTime')
                            ->addViolation();
                    }
                }),
            ],
        ]);
    }
}
