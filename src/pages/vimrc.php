<h1>My .vimrc</h1>

<div class="code">
set nocompatible<br />
behave mswin<br />
<br />
&quot; ================ MSWIN ==================<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Set options and add mapping such that Vim behaves a lot like MS-Windows<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot; Maintainer:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Bram Moolenaar &lt;Bram@vim.org&gt;<br />
&nbsp;&nbsp; &nbsp;&quot; Last change:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2006 Apr 02<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; bail out if this isn&#039;t wanted (mrsvim.vim uses this).<br />
&nbsp;&nbsp; &nbsp;if exists(&quot;g:skip_loading_mswin&quot;) &amp;&amp; g:skip_loading_mswin<br />
&nbsp;&nbsp; &nbsp; &nbsp;finish<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; set the &#039;cpoptions&#039; to its Vim default<br />
&nbsp;&nbsp; &nbsp;if 1&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot; only do this when compiled with expression evaluation<br />
&nbsp;&nbsp; &nbsp; &nbsp;let s:save_cpo = &amp;cpoptions<br />
&nbsp;&nbsp; &nbsp;endif<br />
&nbsp;&nbsp; &nbsp;set cpo&amp;vim<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; set &#039;selection&#039;, &#039;selectmode&#039;, &#039;mousemodel&#039; and &#039;keymodel&#039; for MS-Windows<br />
&nbsp;&nbsp; &nbsp;&quot; behave mswin NOTE: Duplicate.<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; backspace and cursor keys wrap to previous/next line<br />
&nbsp;&nbsp; &nbsp;set backspace=indent,eol,start whichwrap+=&lt;,&gt;,[,]<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; backspace in Visual mode deletes selection<br />
&nbsp;&nbsp; &nbsp;vnoremap &lt;BS&gt; d<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-X and SHIFT-Del are Cut<br />
&nbsp;&nbsp; &nbsp;vnoremap &lt;C-X&gt; &quot;+x<br />
&nbsp;&nbsp; &nbsp;vnoremap &lt;S-Del&gt; &quot;+x<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-C and CTRL-Insert are Copy<br />
&nbsp;&nbsp; &nbsp;&quot;vnoremap &lt;C-C&gt; &quot;+y<br />
&nbsp;&nbsp; &nbsp;vnoremap &lt;C-Insert&gt; &quot;+y<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-V and SHIFT-Insert are Paste<br />
&nbsp;&nbsp; &nbsp;&quot;map &lt;C-V&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;+gP<br />
&nbsp;&nbsp; &nbsp;&quot;map &lt;S-Insert&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&quot;+gP<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;cmap &lt;C-V&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-R&gt;+<br />
&nbsp;&nbsp; &nbsp;cmap &lt;S-Insert&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-R&gt;+<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Pasting blockwise and linewise selections is not possible in Insert and<br />
&nbsp;&nbsp; &nbsp;&quot; Visual mode without the +virtualedit feature. &nbsp;They are pasted as if they<br />
&nbsp;&nbsp; &nbsp;&quot; were characterwise instead.<br />
&nbsp;&nbsp; &nbsp;&quot; Uses the paste.vim autoload script.<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;exe &#039;inoremap &lt;script&gt; &lt;C-V&gt;&#039; paste#paste_cmd[&#039;i&#039;]<br />
&nbsp;&nbsp; &nbsp;&quot;exe &#039;vnoremap &lt;script&gt; &lt;C-V&gt;&#039; paste#paste_cmd[&#039;v&#039;]<br />
<br />
&nbsp;&nbsp; &nbsp;imap &lt;S-Insert&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-V&gt;<br />
&nbsp;&nbsp; &nbsp;vmap &lt;S-Insert&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-V&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Use CTRL-Q to do what CTRL-V used to do<br />
&nbsp;&nbsp; &nbsp;noremap &lt;C-Q&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-V&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; C-S for saving disabled for leetness<br />
&nbsp;&nbsp; &nbsp;&quot; Use CTRL-S for saving, also in Insert mode<br />
&nbsp;&nbsp; &nbsp;&quot;noremap &lt;C-S&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:update&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;&quot;vnoremap &lt;C-S&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-C&gt;:update&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;&quot;inoremap &lt;C-S&gt;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;C-O&gt;:update&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; For CTRL-V to work autoselect must be off.<br />
&nbsp;&nbsp; &nbsp;&quot; On Unix we have two selections, autoselect can be used.<br />
&nbsp;&nbsp; &nbsp;if !has(&quot;unix&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp;set guioptions-=a<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-Y is Redo (although not repeat); not in cmdline though<br />
&nbsp;&nbsp; &nbsp;&quot;noremap &lt;C-Y&gt; &lt;C-R&gt;<br />
&nbsp;&nbsp; &nbsp;&quot;inoremap &lt;C-Y&gt; &lt;C-O&gt;&lt;C-R&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Alt-Space is System menu<br />
&nbsp;&nbsp; &nbsp;if has(&quot;gui&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp;noremap &lt;M-Space&gt; :simalt ~&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp;inoremap &lt;M-Space&gt; &lt;C-O&gt;:simalt ~&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp;cnoremap &lt;M-Space&gt; &lt;C-C&gt;:simalt ~&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-A is Select all<br />
&nbsp;&nbsp; &nbsp;&quot;noremap &lt;C-A&gt; gggH&lt;C-O&gt;G<br />
&nbsp;&nbsp; &nbsp;&quot;inoremap &lt;C-A&gt; &lt;C-O&gt;gg&lt;C-O&gt;gH&lt;C-O&gt;G<br />
&nbsp;&nbsp; &nbsp;&quot;cnoremap &lt;C-A&gt; &lt;C-C&gt;gggH&lt;C-O&gt;G<br />
&nbsp;&nbsp; &nbsp;&quot;onoremap &lt;C-A&gt; &lt;C-C&gt;gggH&lt;C-O&gt;G<br />
&nbsp;&nbsp; &nbsp;&quot;snoremap &lt;C-A&gt; &lt;C-C&gt;gggH&lt;C-O&gt;G<br />
&nbsp;&nbsp; &nbsp;&quot;xnoremap &lt;C-A&gt; &lt;C-C&gt;ggVG<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-Tab is Next window<br />
&nbsp;&nbsp; &nbsp;noremap &lt;C-Tab&gt; &lt;C-W&gt;w<br />
&nbsp;&nbsp; &nbsp;inoremap &lt;C-Tab&gt; &lt;C-O&gt;&lt;C-W&gt;w<br />
&nbsp;&nbsp; &nbsp;cnoremap &lt;C-Tab&gt; &lt;C-C&gt;&lt;C-W&gt;w<br />
&nbsp;&nbsp; &nbsp;onoremap &lt;C-Tab&gt; &lt;C-C&gt;&lt;C-W&gt;w<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; CTRL-F4 is Close window<br />
&nbsp;&nbsp; &nbsp;noremap &lt;C-F4&gt; &lt;C-W&gt;c<br />
&nbsp;&nbsp; &nbsp;inoremap &lt;C-F4&gt; &lt;C-O&gt;&lt;C-W&gt;c<br />
&nbsp;&nbsp; &nbsp;cnoremap &lt;C-F4&gt; &lt;C-C&gt;&lt;C-W&gt;c<br />
&nbsp;&nbsp; &nbsp;onoremap &lt;C-F4&gt; &lt;C-C&gt;&lt;C-W&gt;c<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; restore &#039;cpoptions&#039;<br />
&nbsp;&nbsp; &nbsp;set cpo&amp;<br />
&nbsp;&nbsp; &nbsp;if 1<br />
&nbsp;&nbsp; &nbsp; &nbsp;let &amp;cpoptions = s:save_cpo<br />
&nbsp;&nbsp; &nbsp; &nbsp;unlet s:save_cpo<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&quot; ============= FUNCTIONALITY =============<br />
<br />
&nbsp;&nbsp; &nbsp;set foldmethod=marker<br />
&nbsp;&nbsp; &nbsp;set tabstop=4<br />
&nbsp;&nbsp; &nbsp;set shiftwidth=4<br />
&nbsp;&nbsp; &nbsp;set autoindent<br />
&nbsp;&nbsp; &nbsp;set expandtab <br />
&nbsp;&nbsp; &nbsp;set smartindent<br />
&nbsp;&nbsp; &nbsp;set spell<br />
&nbsp;&nbsp; &nbsp;set backspace=2<br />
&nbsp;&nbsp; &nbsp;set history=100<br />
&nbsp;&nbsp; &nbsp;set wildmenu<br />
&nbsp;&nbsp; &nbsp;set incsearch<br />
&nbsp;&nbsp; &nbsp;set hidden<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Double slash -&gt; Case insensitive search<br />
&nbsp;&nbsp; &nbsp;map // /\c<br />
&nbsp;&nbsp; &nbsp;map ?? ?\c<br />
<br />
&nbsp;&nbsp; &nbsp;command DiffOrig vert new | set bt=nofile | r # | 0d_ | diffthis | wincmd p | diffthis<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; tab navigation like firefox<br />
&nbsp;&nbsp; &nbsp;nmap &lt;C-S-tab&gt; :tabprevious&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;nmap &lt;C-tab&gt; :tabnext&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;map &lt;C-S-tab&gt; :tabprevious&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;map &lt;C-tab&gt; :tabnext&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;imap &lt;C-S-tab&gt; &lt;Esc&gt;:tabprevious&lt;CR&gt;i<br />
&nbsp;&nbsp; &nbsp;imap &lt;C-tab&gt; &lt;Esc&gt;:tabnext&lt;CR&gt;i<br />
&nbsp;&nbsp; &nbsp;&quot;nmap &lt;C-t&gt; :tabnew&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;&quot;imap &lt;C-t&gt; &lt;Esc&gt;:tabnew&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;Ctrl+N New<br />
&nbsp;&nbsp; &nbsp;&quot;nmap &lt;C-n&gt; :tabnew&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;&quot;imap &lt;C-n&gt; &lt;Esc&gt;:tabnew&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; F1 -&gt; Open file in this window<br />
&nbsp;&nbsp; &nbsp;map &lt;F1&gt; &lt;Esc&gt;:browse e&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;imap &lt;F1&gt; &lt;Esc&gt;:browse e&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;nmap &lt;F1&gt; &lt;Esc&gt;:browse e&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; F2 -&gt; Open file in new tab<br />
&nbsp;&nbsp; &nbsp;map &lt;F2&gt; &lt;Esc&gt;:browse tabnew&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;imap &lt;F2&gt; &lt;Esc&gt;:browse tabnew&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;nmap &lt;F2&gt; &lt;Esc&gt;:browse tabnew&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; F3 -&gt; Split window vertically and open file<br />
&nbsp;&nbsp; &nbsp;map &lt;F3&gt; &lt;Esc&gt;:browse vsp&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;imap &lt;F3&gt; &lt;Esc&gt;:browse vsp&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;nmap &lt;F3&gt; &lt;Esc&gt;:browse vsp&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; F4 -&gt; Split window horizontally and open file<br />
&nbsp;&nbsp; &nbsp;map &lt;F4&gt; &lt;Esc&gt;:browse sp&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;imap &lt;F4&gt; &lt;Esc&gt;:browse sp&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;nmap &lt;F4&gt; &lt;Esc&gt;:browse sp&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; TAB on select indents:<br />
&nbsp;&nbsp; &nbsp;&quot;FIXME: Doesn&#039;t always let you press tab twice<br />
&nbsp;&nbsp; &nbsp;smap &lt;Tab&gt; &lt;Esc&gt;:&#039;&lt;,&#039;&gt; &gt; &lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;vmap &lt;Tab&gt; &lt;Esc&gt;:&#039;&lt;,&#039;&gt; &gt; &lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;omnicomplete<br />
&nbsp;&nbsp; &nbsp;autocmd FileType python set omnifunc=pythoncomplete#Complete<br />
&nbsp;&nbsp; &nbsp;autocmd FileType javascript set omnifunc=javascriptcomplete#CompleteJS<br />
&nbsp;&nbsp; &nbsp;autocmd FileType html set omnifunc=htmlcomplete#CompleteTags<br />
&nbsp;&nbsp; &nbsp;autocmd FileType css set omnifunc=csscomplete#CompleteCSS<br />
&nbsp;&nbsp; &nbsp;autocmd FileType xml set omnifunc=xmlcomplete#CompleteTags<br />
&nbsp;&nbsp; &nbsp;autocmd FileType php set omnifunc=phpcomplete#CompletePHP<br />
&nbsp;&nbsp; &nbsp;autocmd FileType c set omnifunc=ccomplete#Complete<br />
&nbsp;&nbsp; &nbsp;autocmd FileType ruby set omnifunc=rubycomplete#Complete<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;Other file dependant stuff<br />
&nbsp;&nbsp; &nbsp;&quot; Use tabs for makefile and python<br />
&nbsp;&nbsp; &nbsp;autocmd FileType python set noexpandtab<br />
&nbsp;&nbsp; &nbsp;autocmd FileType make set noexpandtab<br />
<br />
&nbsp;&nbsp; &nbsp;if has(&quot;unix&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;helptags ~/.vim/doc<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;if has(&quot;unix&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; php documentation<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; Make sure pman is installed:<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; # apt-get install php-pear<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; # pear install doc.php.net/pman<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;autocmd FileType php command -nargs=1 Doc silent !xterm -e pman &lt;args&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;autocmd FileType ruby command -nargs=1 Doc silent !xterm -e ri &lt;args&gt;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;command -nargs=1 Man silent !xterm -e man &lt;args&gt;<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; HEX EDITING<br />
&nbsp;&nbsp; &nbsp;&quot;Source: http://vim.wikia.com/wiki/Improved_hex_editing<br />
&nbsp;&nbsp; &nbsp;command -bar Hex call ToggleHex()<br />
&nbsp;&nbsp; &nbsp;&quot; helper function to toggle hex mode<br />
&nbsp;&nbsp; &nbsp;function ToggleHex()<br />
&nbsp;&nbsp; &nbsp; &nbsp;&quot; hex mode should be considered a read-only operation<br />
&nbsp;&nbsp; &nbsp; &nbsp;&quot; save values for modified and read-only for restoration later,<br />
&nbsp;&nbsp; &nbsp; &nbsp;&quot; and clear the read-only flag for now<br />
&nbsp;&nbsp; &nbsp; &nbsp;let l:modified=&amp;mod<br />
&nbsp;&nbsp; &nbsp; &nbsp;let l:oldreadonly=&amp;readonly<br />
&nbsp;&nbsp; &nbsp; &nbsp;let &amp;readonly=0<br />
&nbsp;&nbsp; &nbsp; &nbsp;let l:oldmodifiable=&amp;modifiable<br />
&nbsp;&nbsp; &nbsp; &nbsp;let &amp;modifiable=1<br />
&nbsp;&nbsp; &nbsp; &nbsp;if !exists(&quot;b:editHex&quot;) || !b:editHex<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; save old options<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let b:oldft=&amp;ft<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let b:oldbin=&amp;bin<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; set new options<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;setlocal binary &quot; make sure it overrides any textwidth, etc.<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let &amp;ft=&quot;xxd&quot;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; set status<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let b:editHex=1<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; switch to hex editor<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;%!xxd<br />
&nbsp;&nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; restore old options<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let &amp;ft=b:oldft<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;if !b:oldbin<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;setlocal nobinary<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;endif<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; set status<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let b:editHex=0<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;&quot; return to normal editing<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;%!xxd -r<br />
&nbsp;&nbsp; &nbsp; &nbsp;endif<br />
&nbsp;&nbsp; &nbsp; &nbsp;&quot; restore values for modified and read only state<br />
&nbsp;&nbsp; &nbsp; &nbsp;let &amp;mod=l:modified<br />
&nbsp;&nbsp; &nbsp; &nbsp;let &amp;readonly=l:oldreadonly<br />
&nbsp;&nbsp; &nbsp; &nbsp;let &amp;modifiable=l:oldmodifiable<br />
&nbsp;&nbsp; &nbsp;endfunction<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; So right click spelling error works.<br />
&nbsp;&nbsp; &nbsp;&quot; FIXME: Only seems to work in windows<br />
&nbsp;&nbsp; &nbsp;nnoremap &lt;RightMouse&gt; &lt;LeftMouse&gt;&lt;RightMouse&gt; <br />
<br />
&nbsp;&nbsp; &nbsp;set formatoptions+=l<br />
&nbsp;&nbsp; &nbsp;set lbr<br />
<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; TODO: :enew, save, save as<br />
&nbsp;&nbsp; &nbsp;&quot; Ctrl+N =&gt; enew<br />
&nbsp;&nbsp; &nbsp;&quot; Ctrl+S =&gt; Save TODO: Make ctrl+s do same thing as file save<br />
&nbsp;&nbsp; &nbsp;&quot; Ctrl+Shift+S =&gt; Save as?<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; TODO: buffer management (bnext, bprev, new buffer, close buffer, etc)<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; TODO: build with make (look at :make)<br />
&nbsp;&nbsp; &nbsp;&quot; TODO: run?<br />
&nbsp;&nbsp; &nbsp;&quot; TODO: get errors from gcc/php and have next/prev err feature<br />
<br />
&nbsp;&nbsp; &nbsp;set ttyfast<br />
<br />
&quot; ================ VISUAL =================<br />
<br />
&nbsp;&nbsp; &nbsp;colorscheme dw_cyan <br />
&nbsp;&nbsp; &nbsp;syntax on<br />
<br />
&nbsp;&nbsp; &nbsp;if has(&quot;gui_running&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;set lines=32 columns=120<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;set shellslash<br />
&nbsp;&nbsp; &nbsp;set novb<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Fix GNOME Mouse Hide Bug<br />
&nbsp;&nbsp; &nbsp;set nomousehide<br />
<br />
&nbsp;&nbsp; &nbsp;set mouse=a<br />
&nbsp;&nbsp; &nbsp;set guioptions-=T<br />
&nbsp;&nbsp; &nbsp;set guioptions-=m<br />
&nbsp;&nbsp; &nbsp;set nu<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;if has(&quot;7.3&quot;) &quot;FIXME: Enable this automatically for 7.3+<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;set cc=80<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;hi ColorColumn ctermbg=Gray ctermfg=Black guibg=#404040<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;command Skinny set cc=73<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;command Wide set cc=80<br />
&nbsp;&nbsp; &nbsp;&quot;endif<br />
<br />
&nbsp;&nbsp; &nbsp;if has(&quot;unix&quot;)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;set guifont=Monospace\ 10<br />
&nbsp;&nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;set guifont=Lucida_Console:h10:cANSI<br />
&nbsp;&nbsp; &nbsp;endif<br />
<br />
&nbsp;&nbsp; &nbsp;highlight Cursor guifg=white guibg=Gray<br />
&nbsp;&nbsp; &nbsp;highlight iCursor guifg=Gray guibg=white<br />
<br />
&nbsp;&nbsp; &nbsp;set laststatus=2 <br />
&nbsp;&nbsp; &nbsp;set statusline=%n:\ %F\ [%{&amp;ff}]%y%m%h%w%r\ %=[0x%B\|%b]\ \ \ [%c][%l/%L]\ (%p%%)<br />
&nbsp;&nbsp; &nbsp;hi StatusLine cterm=NONE ctermbg=darkgray ctermfg=gray guibg=#202020 guifg=white<br />
<br />
<br />
&nbsp;&nbsp; &nbsp;set cursorline<br />
&nbsp;&nbsp; &nbsp;hi CursorLine cterm=NONE ctermbg=darkgray guibg=#101520<br />
&nbsp;&nbsp; &nbsp;hi CursorColumn cterm=NONE ctermbg=darkgray guibg=#101520<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; $ for change command instead of deleting word then insert<br />
&nbsp;&nbsp; &nbsp;set cpoptions+=$<br />
&nbsp;&nbsp; &nbsp;&quot;set virtualedit=all<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Highlight search terms<br />
&nbsp;&nbsp; &nbsp;set hlsearch <br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Skip the splash screen<br />
&nbsp;&nbsp; &nbsp;set shortmess+=I<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Smooth Scroll<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot; Remamps <br />
&nbsp;&nbsp; &nbsp;&quot; &nbsp;&lt;C-U&gt;<br />
&nbsp;&nbsp; &nbsp;&quot; &nbsp;&lt;C-D&gt;<br />
&nbsp;&nbsp; &nbsp;&quot; &nbsp;&lt;C-F&gt;<br />
&nbsp;&nbsp; &nbsp;&quot; &nbsp;&lt;C-B&gt;<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot; to allow smooth scrolling of the window. I find that quick changes of<br />
&nbsp;&nbsp; &nbsp;&quot; context don&#039;t allow my eyes to follow the action properly.<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot; The global variable g:scroll_factor changes the scroll speed.<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot; Written by Brad Phelan 2006<br />
&nbsp;&nbsp; &nbsp;&quot; http://xtargets.com<br />
&nbsp;&nbsp; &nbsp;let g:scroll_factor = 5000<br />
&nbsp;&nbsp; &nbsp;function! SmoothScroll(dir, windiv, factor)<br />
&nbsp;&nbsp; &nbsp; &nbsp; let wh=winheight(0)<br />
&nbsp;&nbsp; &nbsp; &nbsp; let i=0<br />
&nbsp;&nbsp; &nbsp; &nbsp; while i &lt; wh / a:windiv<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;let t1=reltime()<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;let i = i + 1<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if a:dir==&quot;d&quot;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; normal j<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;else<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; normal k<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;end<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;redraw<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;while 1<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; let t2=reltime(t1,reltime())<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; if t2[1] &gt; g:scroll_factor * a:factor<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;break<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; endif<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;endwhile<br />
&nbsp;&nbsp; &nbsp; &nbsp; endwhile<br />
&nbsp;&nbsp; &nbsp;endfunction<br />
&nbsp;&nbsp; &nbsp;map &lt;C-D&gt; :call SmoothScroll(&quot;d&quot;,2, 2)&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;map &lt;C-U&gt; :call SmoothScroll(&quot;u&quot;,2, 2)&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;map &lt;C-F&gt; :call SmoothScroll(&quot;d&quot;,1, 1)&lt;CR&gt;<br />
&nbsp;&nbsp; &nbsp;map &lt;C-B&gt; :call SmoothScroll(&quot;u&quot;,1, 1)&lt;CR&gt;<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; This script provides a function to activate a vim buffer by passing it the<br />
&nbsp;&nbsp; &nbsp;&quot; position in the buffers list and maps it to &lt;M-number&gt; to easily switch<br />
&nbsp;&nbsp; &nbsp;&quot; between open buffers.<br />
&nbsp;&nbsp; &nbsp;&quot;<br />
&nbsp;&nbsp; &nbsp;&quot; This is best used togheter with the buftabs plugin:<br />
&nbsp;&nbsp; &nbsp;&quot; &nbsp; http://www.vim.org/scripts/script.php?script_id=1664<br />
<br />
&nbsp;&nbsp; &nbsp;function! BufPos_ActivateBuffer(num)<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;let l:count = 1<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;for i in range(1, bufnr(&quot;$&quot;))<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if buflisted(i) &amp;&amp; getbufvar(i, &quot;&amp;modifiable&quot;) <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;if l:count == a:num<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;exe &quot;buffer &quot; . i<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;return <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;endif<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;let l:count = l:count + 1<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;endif<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;endfor<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;echo &quot;No buffer!&quot;<br />
&nbsp;&nbsp; &nbsp;endfunction<br />
<br />
&nbsp;&nbsp; &nbsp;function! BufPos_Initialize()<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;for i in range(1, 9) <br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;exe &quot;map &lt;M-&quot; . i . &quot;&gt; :call BufPos_ActivateBuffer(&quot; . i . &quot;)&lt;CR&gt;&quot;<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;endfor<br />
&nbsp;&nbsp; &nbsp; &nbsp; &nbsp;exe &quot;map &lt;M-0&gt; :call BufPos_ActivateBuffer(10)&lt;CR&gt;&quot;<br />
&nbsp;&nbsp; &nbsp;endfunction<br />
<br />
&nbsp;&nbsp; &nbsp;autocmd VimEnter * call BufPos_Initialize()<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; Don&#039;t update the display while executing macros<br />
&nbsp;&nbsp; &nbsp;set lazyredraw<br />
<br />
&nbsp;&nbsp; &nbsp;&quot; When the page starts to scroll, keep the cursor 8 lines from the top and 8<br />
&nbsp;&nbsp; &nbsp;&quot; lines from the bottom<br />
&nbsp;&nbsp; &nbsp;set scrolloff=4<br />
&nbsp;&nbsp; &nbsp;<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: some option to easily enable/disable forced wrapping<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: macro for re-loading .vimrc<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: make a :help ascii to get an ascii table<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: tagging<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: HTML/CSS help doc?<br />
&nbsp;&nbsp; &nbsp;&quot; &nbsp; &nbsp; &nbsp; - common &amp;quot; things<br />
&nbsp;&nbsp; &nbsp;&quot;FIXME: ruby comments get bpushed to the left<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: autosave<br />
<br />
&nbsp;&nbsp; &nbsp;&quot;TODO: PHP syntax checking (make), and jump to first error<br />
&nbsp;&nbsp; &nbsp;set spell<br />

</div>
