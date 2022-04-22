<?php
namespace App\Component\Html;

final class ArrayToTable
{
    private array $data;
    private string $h3;
    private string $footer;

    /**
     * ArrayToTable constructor.
     * @param array $data
     * @param string $h3
     * @param string $footer
     */
    public function __construct(array $data, string $h3, string $footer)
    {
        $this->data = $data;
        $this->h3 = $h3;
        $this->footer = $footer;
    }

    public function __invoke(): string
    {
        if(!$count = count($this->data)) return "<h3>$this->h3 - (0)</h3>";

        $titles = array_keys($this->data[0] ?? []);
        $ntitles = count($titles)+1;

        $html = [
            "<hr/>",
            "<br/>",
            "<h3>$this->h3 ($count)</h3>",
            "<table>"
        ];
        $tmp = ["<th>Nº</th>"];
        foreach ($titles as $title) {
            $tmp[] = "<th>$title</th>";
        }
        $tmp = implode("", $tmp);
        $html[] = "<tr>$tmp</tr>";

        foreach ($this->data as $i=>$row) {
            $tmp = ["<td>{$i}</td>"];
            foreach ($titles as $field) {
                $value = $row[$field];
                $value = htmlentities($value);
                $tmp[] = "<td>{$value}</td>";
            }
            $tmp = implode("", $tmp);
            $html[] = "<tr>$tmp</tr>";
        }

        if ($this->footer)
            $html[] = "<tr><td colspan=\"$ntitles\">$this->footer</td></tr>";

        $html[] = "</table>";
        return implode("\n", $html);
    }
}