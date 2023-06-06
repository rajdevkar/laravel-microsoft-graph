<?php

namespace Dcblogdev\MsGraph\Resources;

use Dcblogdev\MsGraph\Facades\MsGraph;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class Contacts extends MsGraph
{
    private $total;
    private $top;
    private $skip;

    public function total($total)
    {
        $this->total = $total;

        return $this;
    }

    public function top($top)
    {
        $this->top = $top;

        return $this;
    }

    public function skip($skip)
    {
        $this->skip = $skip;

        return $this;
    }

    public function get($params = [])
    {
        $total  = request('total', $this->total ?? 0);
        $top  = request('top', $this->top ?? 0);
        $skip = request('skip', $this->skip ?? 0);

        $page = $params['page'];
        $perPage = $params['top'];
        $_skip = $params['skip'];

        if ($params == []) {
            $params = http_build_query([
                '$orderby' => 'displayName',
                '$top'     => $top,
                '$skip'    => $skip,
                '$count'   => 'true',
            ]);
        } else {
            $params = http_build_query($params);
        }

        $contacts = MsGraph::get('me/contacts?'.$params);

        $data = MsGraph::getPagination($contacts, $total, $perPage, $_skip);

        $data = $contacts['value'];
        $totalCount = $contacts['@odata.count'];
        $collection = new Collection($data);

        $paginator = new LengthAwarePaginator(
            $collection->forPage(1, $perPage),
            $totalCount,
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );
        if(isset($contacts['@odata.nextLink'])) $paginator->withPath($contacts['@odata.nextLink']);

        return [
            'data'     => $paginator,
            'total'    => $total,
            'top'      => $top,
            'skip'     => $skip,
        ];
    }

    public function find($id)
    {
        return MsGraph::get("me/contacts/$id");
    }

    public function store(array $data)
    {
        return MsGraph::post('me/contacts', $data);
    }

    public function update($id, array $data)
    {
        return MsGraph::patch("me/contacts/$id", $data);
    }

    public function delete($id)
    {
        return MsGraph::delete("me/contacts/$id");
    }
}
