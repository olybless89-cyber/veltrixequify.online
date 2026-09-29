<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Gateway;
use App\Models\Language;
use App\Models\Template;
use App\Models\ManagePlan;
use App\Models\Subscriber;
use App\Http\Traits\Notify;
use Illuminate\Http\Request;
use App\Models\ContentDetails;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use App\Console\Commands\UpdateBadgeCron;
class FrontendController extends Controller
{
    use Notify;

    public function __construct()
    {
        $this->theme = template();
    }

    public function index()
    {
        $templateSection = ['hero', 'about-us', 'why-chose-us', 'how-it-work', 'how-we-work', 'know-more-us', 'deposit-withdraw', 'news-letter', 'news-letter-referral', 'testimonial', 'request-a-call', 'investor', 'blog', 'faq', 'we-accept', 'investment'];
        $data['templates'] = Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name');
        $contentSection = ['feature', 'why-chose-us', 'how-it-work', 'how-we-work', 'know-more-us', 'testimonial', 'investor', 'blog', 'faq'];
        $data['contentDetails'] = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');
        $data['gateways'] = Gateway::all();
        $data['plans'] = ManagePlan::where(['status' => 1, 'featured' => 1])->get();
        return view($this->theme . 'home', $data);
    }


    public function about()
    {
        $templateSection = ['about-us', 'investor', 'faq', 'we-accept', 'how-it-work', 'how-we-work', 'know-more-us', 'why-chose-us', 'testimonial', 'news-letter'];
        $data['templates'] = Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name');

        $contentSection = ['feature', 'why-chose-us', 'investor', 'faq', 'how-it-work', 'how-we-work', 'know-more-us', 'testimonial'];
        $data['contentDetails'] = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');
        $data['gateways'] = Gateway::all();
        return view($this->theme . 'about', $data);
    }


    public function blog()
    {
        $data['title'] = "Blog";
        $contentSection = ['blog'];

        $templateSection = ['blog', 'news-letter'];
        $data['templates'] = Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name');

        $data['contentDetails'] = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');
        return view($this->theme . 'blog', $data);
    }

    public function blogDetails($slug = null, $id = null)
    {
        $getData = Content::findOrFail($id);

        $contentSection = [$getData->name];
        $contentDetail = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->where('content_id', $getData->id)
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');

        $singleItem['title'] = @$contentDetail[$getData->name][0]->description->title;
        $singleItem['description'] = @$contentDetail[$getData->name][0]->description->description;
        $singleItem['date'] = dateTime(@$contentDetail[$getData->name][0]->created_at, 'd M, Y');
        $singleItem['image'] = getFile(config('location.content.path') . @$contentDetail[$getData->name][0]->content->contentMedia->description->image);


        $contentSectionPopular = ['blog'];
        $popularContentDetails = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->where('content_id', '!=', $getData->id)
            ->whereHas('content', function ($query) use ($contentSectionPopular) {
                return $query->whereIn('name', $contentSectionPopular);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');

        return view($this->theme . 'blogDetails', compact('singleItem', 'popularContentDetails'));
    }


    public function faq()
    {

        $templateSection = ['faq', 'news-letter'];
        $data['templates'] = Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name');

        $contentSection = ['faq'];
        $data['contentDetails'] = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');

        $data['increment'] = 1;
        return view($this->theme . 'faq', $data);
    }

    public function contact()
    {
        $templateSection = ['contact-us', 'news-letter'];
        $data['templates'] = $templates = Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name');
        $title = 'Contact Us';
        $contact = @$templates['contact-us'][0]->description;

        return view($this->theme . 'contact',  $data, compact('title', 'contact'));
    }

    public function contactSend(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|max:91',
            'subject' => 'required|max:100',
            'message' => 'required|max:1000',
        ]);
        $requestData = Purify::clean($request->except('_token', '_method'));

        $basic = (object)config('basic');
        $basicEmail = $basic->sender_email;

        $name = $requestData['name'];
        $email_from = $requestData['email'];
        $subject = $requestData['subject'];
        $message = $requestData['message']."<br>Regards<br>".$name;
        $from = $email_from;

        $headers = "From: <$from> \r\n";
        $headers .= "Reply-To: <$from> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        $to = $basicEmail;

        if (@mail($to, $subject, $message, $headers)) {
            // echo 'Your message has been sent.';
        } else {
            //echo 'There was a problem sending the email.';
        }

        return back()->with('success', 'Mail has been sent');
    }

    public function getLink($getLink = null, $id = null)
    {
        $getData = Content::findOrFail($id);

        $contentSection = [$getData->name];
        $contentDetail = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->where('content_id', $getData->id)
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');

        $title = @$contentDetail[$getData->name][0]->description->title;
        $description = @$contentDetail[$getData->name][0]->description->description;
        return view($this->theme . 'getLink', compact('contentDetail', 'title', 'description'));
    }

    public function subscribe(Request $request)
    {
        $rules = [
            'email' => 'required|email|max:255|unique:subscribers'
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect(url()->previous() . '#subscribe')->withErrors($validator);
        }
        $data = new Subscriber();
        $data->email = $request->email;
        $data->save();

        $msg = [
            'email' => $data->email
        ];

        $action = [
            "link" => route('admin.subscriber.index'),
            "icon" => "fas fa-user text-white"
        ];

        $this->adminPushNotification('SUBSCRIBE_NEWSLETTER', $msg, $action);
        $this->mailToAdmin($type = 'SUBSCRIBE_NEWSLETTER', [
            'email' => $data->email,
        ]);

        return redirect(url()->previous() . '#subscribe')->with('success', 'Subscribed Successfully');
    }

    public function language($code)
    {
        $language = Language::where('short_name', $code)->first();
        if (!$language) $code = 'US';
        session()->put('trans', $code);
        session()->put('rtl', $language ? $language->rtl : 0);
        return redirect()->back();
    }


    public function planList()
    {
        if (auth()->user()) {
            $data['extend_blade'] = $this->theme . 'layouts.user';
        } else {
            $data['extend_blade'] = $this->theme . 'layouts.app';
        }

        $data['plans'] = ManagePlan::where('status', 1)->get();

        $templateSection = ['investment', 'calculate-profit', 'faq', 'we-accept', 'deposit-withdraw', 'why-chose-us', 'news-letter'];
        $data['templates'] = Template::templateMedia()->whereIn('section_name', $templateSection)->get()->groupBy('section_name');

        $contentSection = ['investment', 'calculate-profit', 'faq', 'we-accept', 'deposit-withdraw', 'why-chose-us'];
        $data['contentDetails'] = ContentDetails::select('id', 'content_id', 'description', 'created_at')
            ->whereHas('content', function ($query) use ($contentSection) {
                return $query->whereIn('name', $contentSection);
            })
            ->with(['content:id,name',
                'content.contentMedia' => function ($q) {
                    $q->select(['content_id', 'description']);
                }])
            ->get()->groupBy('content.name');

        session()->forget('amount');
        session()->forget('plan_id');
        $data['gateways'] = Gateway::all();

        return view($this->theme . 'plan', $data);

    }

    public function calculator()
    {
        $data['title'] = 'Investment Profit Calculator';
        $data['plans'] = ManagePlan::where('status', 1)->orderBy('name')->get();
        return view($this->theme . 'calculator', $data);
    }

    public function calculateProfitApi(Request $request)
    {
        $planId = $request->query('plan_id');
        $amount = (float) $request->query('amount', 0);

        $plan = ManagePlan::where('id', $planId)->where('status', 1)->first();
        if (!$plan) {
            return response()->json(['error' => 'Plan not found'], 404);
        }

        $min = (float) $plan->minimum_amount;
        $max = (float) $plan->maximum_amount;
        $fixed = (float) $plan->fixed_amount;

        if ($fixed > 0) {
            $amount = $fixed;
        }

        $currency = config('basic.currency_symbol', '$');
        $profitPerCycle = ($plan->profit_type == 1) ? ($amount * $plan->profit) / 100 : (float) $plan->profit;
        $cycles = ($plan->is_lifetime == 1) ? 'Lifetime' : (int) $plan->repeatable;
        $totalProfit = ($plan->is_lifetime == 1) ? 'Lifetime Earnings' : ($profitPerCycle * (int) $plan->repeatable);
        $capitalBack = (bool) $plan->is_capital_back;
        $totalReturn = ($plan->is_lifetime == 1) 
            ? 'Lifetime Returns' 
            : ($totalProfit + ($capitalBack ? $amount : 0));

        $netRoiPercent = ($amount > 0 && is_numeric($totalProfit)) 
            ? round(($totalProfit / $amount) * 100, 2) 
            : round($plan->profit, 2);

        return response()->json([
            'plan_name' => $plan->name,
            'invested_amount' => $amount,
            'invested_amount_formatted' => $currency . number_format($amount, 2),
            'profit_rate' => $plan->profit . ($plan->profit_type == 1 ? '%' : ' ' . $currency),
            'schedule_hours' => $plan->schedule,
            'repeatable_times' => $cycles,
            'profit_per_cycle' => $profitPerCycle,
            'profit_per_cycle_formatted' => $currency . number_format($profitPerCycle, 2),
            'total_profit' => is_numeric($totalProfit) ? $totalProfit : $totalProfit,
            'total_profit_formatted' => is_numeric($totalProfit) ? $currency . number_format($totalProfit, 2) : $totalProfit,
            'capital_back' => $capitalBack,
            'total_return_formatted' => is_numeric($totalReturn) ? $currency . number_format($totalReturn, 2) : $totalReturn,
            'net_roi_percent' => $netRoiPercent . '%',
        ]);
    }
}
