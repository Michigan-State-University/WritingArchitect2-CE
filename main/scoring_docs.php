<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>

<body>
  <div id="content"></div>
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
  <script>
    // const mdText = fetch('scoring-docs.md');
    const md = `
Marked - Markdown Parser
========================

[Marked] lets you convert [Markdown] into HTML.  Markdown is a simple text format whose goal is to be very easy to read and write, even when not converted to HTML.  This demo page will let you type anything you like and see how it gets converted.  Live.  No more waiting around.

How To Use The Demo
-------------------

1. Type in stuff on the left.
2. See the live updates on the right.

That's it.  Pretty simple.  There's also a drop-down option in the upper right to switch between various views:

- **Preview:**  A live display of the generated HTML as it would render in a browser.
- **HTML Source:**  The generated HTML before your browser makes it pretty.
- **Lexer Data:**  What [marked] uses internally, in case you like gory stuff like this.
- **Quick Reference:**  A brief run-down of how to format things using markdown.

Why Markdown?
-------------

It's easy.  It's not overly bloated, unlike HTML.  Also, as the creator of [markdown] says,

> The overriding design goal for Markdown's
> formatting syntax is to make it as readable
> as possible. The idea is that a
> Markdown-formatted document should be
> publishable as-is, as plain text, without
> looking like it's been marked up with tags
> or formatting instructions.

Ready to start writing?  Either start changing stuff on the left or
[clear everything](/demo/?text=) with a simple click.

[Marked]: https://github.com/markedjs/marked/
[Markdown]: http://daringfireball.net/projects/markdown/`;
    // const md = File(mdText).parse();
    document.getElementById('content').innerHTML =
      marked.parse(md);
  </script>
</body>

</html>