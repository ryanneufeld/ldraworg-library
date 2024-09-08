<?php

namespace App\Filament\Part\Actions;

use App\Events\PartRenamed;
use App\Events\PartSubmitted;
use App\LDraw\PartManager;
use App\Models\Part;
use App\Models\PartHistory;
use App\Models\PartType;
use Closure;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;

class EditNumberAction
{
    public static function make(Part $part, ?string $name = null): EditAction
    {
        return EditAction::make($name)
            ->label('Renumber/Move')
            ->modalHeading('Move/Renumber Part')
            ->record($part)
            ->form(self::formSchema($part))
            ->successNotificationTitle('Renumber/Move Successful')
            ->using(fn (Part $p, array $data) => self::updateMove($p, $data))
            ->visible(Auth::user()?->can('move', $part) ?? false);
    }

    protected static function formSchema(Part $part): array
    {
        return [
            TextInput::make('folder')
                ->label('Current Location')
                ->placeholder($part->type->folder)
                ->disabled(),
            TextInput::make('name')
                ->label('Current Name')
                ->placeholder(basename($part->filename))
                ->disabled(),
            Radio::make('part_type_id')
                ->label('Select destination folder:')
                ->options(function () use ($part): array {
                    $options = [];
                    foreach (PartType::where('format', $part->type->format)->pluck('folder', 'id')->unique() as $id => $option) {
                        $types = implode(', ', PartType::where('folder', $option)->pluck('name')->all());
                        $options[$id] = "{$option} ({$types})";
                    }

                    return $options;
                }),
            TextInput::make('newname')
                ->label('New Name')
                ->helperText('Exclude the folder from the name')
                ->nullable()
                ->string()
                ->rules([
                    fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get, $part) {
                        if (! empty($get('part_type_id'))) {
                            $newType = PartType::find($get('part_type_id'));
                            $p = Part::find($part->id);
                            if (! empty($newType) && ! empty($p)) {
                                $newName = basename($value, ".{$p->type->format}");
                                $newName = "{$newType->folder}{$newName}.{$newType->format}";
                                $oldp = Part::firstWhere('filename', $newName);
                                if (! is_null($oldp)) {
                                    $fail($newName.' already exists');
                                }
                            }
                        }
                    },
                ]),
        ];
    }

    protected static function updateMove(Part $part, array $data): Part
    {
        $manager = app(PartManager::class);
        $newType = PartType::find($data['part_type_id']);
        $newName = basename($data['newname'], ".{$part->type->format}");
        $newName = "{$newName}.{$newType->format}";
        if ($part->isUnofficial()) {
            $oldname = $part->filename;
            $manager->movePart($part, $newName, $newType);
            $part->refresh();
            PartRenamed::dispatch($part, Auth::user(), $oldname, $part->filename);
        } else {
            $upart = Part::unofficial()->where('filename', "{$newType->folder}$newName")->first();
            if (is_null($upart)) {
                $upart = $manager->copyOfficialToUnofficialPart($part);
                PartHistory::create([
                    'part_id' => $upart->id,
                    'user_id' => Auth::user()->id,
                    'comment' => 'Moved from '.$part->name(),
                ]);
                $upart->refresh();
                $manager->movePart($upart, $newName, $newType);
                PartSubmitted::dispatch($upart, Auth::user());
            }
            $mpart = $manager->addMovedTo($part, $upart);
            $part->unofficial_part->associate($mpart);
            $part->save();
            $mpart->save();
            PartSubmitted::dispatch($mpart, Auth::user());
        }

        return $part;
    }
}
