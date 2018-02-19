# Habbowidgets.com Language files

This project contains the language files for [Habbowidgets.com](https://www.habbowidgets.com).

With this project you can help update the translation for each language used.

## Contributing

If you want to contribute, you can easily create a github.com account and
use their online editor to change the files. It's also perfectly fine to fork
the project and use git commands to update it if you're comfortable with that.

Once you've finished please submit a [pull request](https://help.github.com/articles/about-pull-requests/).
(If you see an open pull request in your language, don't hesitate to review it, but be constructive.)

If you want your Habbo name on the [About page](https://www.habbowidgets.com/about), 
please put your hotel and name in the pull request that you create.

Check out the open issues if there's something to be translated!

### Rules

To prevent a lot of back and forth on specific translations, please think twice
before you want to fix an already translated part.

For the chosen dialect we use the most common one found in the hotel, or that
of the originating country. This means for example American-English and 
Brazilian-Portugese. It's also partly because technically the language is tied 
to the domain extension of the hotel.

### Technicalities

The translation files are PHP files, although you can easily open these with
your favourite editor. 

- Please keep in mind that we use UTF-8 for the encoding,
so if you're not sure if your editor handles this, use "Save As" functionality
and pick the UTF-8 encoding option.
- Some strings contain either HTML (recognizable by <> tags) or placeholders like 
"%d" or "%s". Keep these parts in tact, they are used within the code.
You might want to reorganize them based on proper grammar though.
- Some lines contain comments (recognizable by the double slash "//" symbols),
these give extra information about placeholders or about how a sentence should work.

