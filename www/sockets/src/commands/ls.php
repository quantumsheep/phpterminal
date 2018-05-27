<?php
namespace Alph\Commands;

use Alph\Services\CommandInterface;
use Alph\Services\SenderData;
use Ratchet\ConnectionInterface;

class ls implements CommandInterface
{
    const USAGE = "ls [OPTION]... [FILE]...";

    const SHORT_DESCRIPTION = "List information about the FILEs (the current directory by default).
    Sort entries alphabetically if none of -cftuvSUX nor --sort is specified.
    Mandatory arguments to long options are mandatory for short options too.";

    const FULL_DESCRIPTION = "The SIZE argument is an integer and optional unit (example: 10K is 10*1024).
    Units are K,M,G,T,P,E,Z,Y (powers of 1024) or KB,MB,... (powers of 1000).
    
    Using color to distinguish file types is disabled both by default and
    with --color=never.  With --color=auto, ls emits color codes only when
    standard output is connected to a terminal.  The LS_COLORS environment
    variable can change the settings.  Use the dircolors command to set it.
    
    GNU coreutils online help: <http://www.gnu.org/software/coreutils/>
    Full documentation at: <http://www.gnu.org/software/coreutils/ls>
    or available locally via: info '(coreutils) ls invocation'";

    const OPTIONS = [
        "-a, --all" => "do not ignore entries starting with .",
    ];

    const EXIT_STATUS = "0  if OK,
    1  if minor problems (e.g., cannot access subdirectory),
    2  if serious trouble (e.g., cannot access command-line argument).";

//   -A, --almost-all           do not list implied . and ..
//       --author               with -l, print the author of each file
//   -b, --escape               print C-style escapes for nongraphic characters
//       --block-size=SIZE      scale sizes by SIZE before printing them; e.g.,
//                                '--block-size=M' prints sizes in units of
//                                1,048,576 bytes; see SIZE format below
//   -B, --ignore-backups       do not list implied entries ending with ~
//   -c                         with -lt: sort by, and show, ctime (time of last
//                                modification of file status information);
//                                with -l: show ctime and sort by name;
//                                otherwise: sort by ctime, newest first
//   -C                         list entries by columns
//       --color[=WHEN]         colorize the output; WHEN can be 'always' (default
//                                if omitted), 'auto', or 'never'; more info below
//   -d, --directory            list directories themselves, not their contents
//   -D, --dired                generate output designed for Emacs' dired mode
//   -f                         do not sort, enable -aU, disable -ls --color
//   -F, --classify             append indicator (one of */=>@|) to entries
//       --file-type            likewise, except do not append '*'
//       --format=WORD          across -x, commas -m, horizontal -x, long -l,
//                                single-column -1, verbose -l, vertical -C
//       --full-time            like -l --time-style=full-iso
//   -g                         like -l, but do not list owner
//       --group-directories-first
//                              group directories before files;
//                                can be augmented with a --sort option, but any
//                                use of --sort=none (-U) disables grouping
//   -G, --no-group             in a long listing, don't print group names
//   -h, --human-readable       with -l and/or -s, print human readable sizes
//                                (e.g., 1K 234M 2G)
//       --si                   likewise, but use powers of 1000 not 1024
//   -H, --dereference-command-line
//                              follow symbolic links listed on the command line
//       --dereference-command-line-symlink-to-dir
//                              follow each command line symbolic link
//                                that points to a directory
//       --hide=PATTERN         do not list implied entries matching shell PATTERN
//                                (overridden by -a or -A)
//       --indicator-style=WORD  append indicator with style WORD to entry names:
//                                none (default), slash (-p),
//                                file-type (--file-type), classify (-F)
//   -i, --inode                print the index number of each file
//   -I, --ignore=PATTERN       do not list implied entries matching shell PATTERN
//   -k, --kibibytes            default to 1024-byte blocks for disk usage
//   -l                         use a long listing format
//   -L, --dereference          when showing file information for a symbolic
//                                link, show information for the file the link
//                                references rather than for the link itself
//   -m                         fill width with a comma separated list of entries
//   -n, --numeric-uid-gid      like -l, but list numeric user and group IDs
//   -N, --literal              print entry names without quoting
//   -o                         like -l, but do not list group information
//   -p, --indicator-style=slash
//                              append / indicator to directories
//   -q, --hide-control-chars   print ? instead of nongraphic characters
//       --show-control-chars   show nongraphic characters as-is (the default,
//                                unless program is 'ls' and output is a terminal)
//   -Q, --quote-name           enclose entry names in double quotes
//       --quoting-style=WORD   use quoting style WORD for entry names:
//                                literal, locale, shell, shell-always,
//                                shell-escape, shell-escape-always, c, escape
//   -r, --reverse              reverse order while sorting
//   -R, --recursive            list subdirectories recursively
//   -s, --size                 print the allocated size of each file, in blocks
//   -S                         sort by file size, largest first
//       --sort=WORD            sort by WORD instead of name: none (-U), size (-S),
//                                time (-t), version (-v), extension (-X)
//       --time=WORD            with -l, show time as WORD instead of default
//                                modification time: atime or access or use (-u);
//                                ctime or status (-c); also use specified time
//                                as sort key if --sort=time (newest first)
//       --time-style=STYLE     with -l, show times using style STYLE:
//                                full-iso, long-iso, iso, locale, or +FORMAT;
//                                FORMAT is interpreted like in 'date'; if FORMAT
//                                is FORMAT1<newline>FORMAT2, then FORMAT1 applies
//                                to non-recent files and FORMAT2 to recent files;
//                                if STYLE is prefixed with 'posix-', STYLE
//                                takes effect only outside the POSIX locale
//   -t                         sort by modification time, newest first
//   -T, --tabsize=COLS         assume tab stops at each COLS instead of 8
//   -u                         with -lt: sort by, and show, access time;
//                                with -l: show access time and sort by name;
//                                otherwise: sort by access time, newest first
//   -U                         do not sort; list entries in directory order
//   -v                         natural sort of (version) numbers within text
//   -w, --width=COLS           set output width to COLS.  0 means no limit
//   -x                         list entries by lines instead of by columns
//   -X                         sort alphabetically by entry extension
//   -Z, --context              print any security context of each file
//   -1                         list one file per line.  Avoid '\n' with -q or -b
//       --help     display this help and exit
//       --version  output version information and exit

    /**
     * Call the command
     *
     * @param \PDO $db
     * @param \SplObjectStorage $clients
     * @param ConnectionInterface $sender
     * @param string $sess_id
     * @param string $cmd   ²
     */
    public static function call(\PDO $db, \SplObjectStorage $clients, SenderData &$data, ConnectionInterface $sender, string $sess_id, array $sender_session, string $terminal_mac, string $cmd, $parameters)
    {
        $getIdDirectory = $db->prepare("SELECT IdDirectoryFromPath(:paths, :mac) as id");
        $getIdDirectory->bindParam(":mac", $terminal_mac);
        $getIdDirectory->bindParam(":paths", $data->position);
        $getIdDirectory->execute();
        $Path = $getIdDirectory->fetch(\PDO::FETCH_ASSOC)["id"];
        $currentPath = $Path[0];

        if (is_null($Path[0])) {
            $getFiles = $db->prepare("SELECT name FROM TERMINAL_FILE WHERE terminal=:mac AND parent IS NULL");
            $getFiles->bindParam(":mac", $terminal_mac);
            $getFiles->execute();
            $files = $getFiles->fetchAll(\PDO::FETCH_COLUMN);

            $getDirs = $db->prepare("SELECT name FROM TERMINAL_DIRECTORY WHERE terminal=:mac AND parent IS NULL");
            $getDirs->bindParam(":mac", $terminal_mac);
            $getDirs->execute();
            $dirs = $getDirs->fetchAll(\PDO::FETCH_COLUMN);
        } else {
            var_dump($Path[0]);
            $getFiles = $db->prepare("SELECT name FROM TERMINAL_FILE WHERE terminal=:mac AND parent=:parent");
            $getFiles->bindParam(":mac", $terminal_mac);
            $getFiles->bindParam(":parent", $currentPath);
            $getFiles->execute();
            $files = $getFiles->fetchAll(\PDO::FETCH_COLUMN);

            $getDirs = $db->prepare("SELECT name FROM TERMINAL_DIRECTORY WHERE terminal=:mac AND parent=:parent");
            $getDirs->bindParam(":mac", $terminal_mac);
            $getDirs->bindParam(":parent", $currentPath);
            $getDirs->execute();
            $dirs = $getDirs->fetchAll(\PDO::FETCH_COLUMN);
        }

        foreach ($files as $file) {
            $sender->send("message|<br>file : " . $file);
        }

        foreach ($dirs as $dir) {
            $sender->send("message|<br>dir : " . $dir);
        }
    }
}
