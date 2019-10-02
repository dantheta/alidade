#!/usr/bin/python

import argparse
import requests

step = None


# Sequence of test method, URL, post data, success condition
# Test data can use lambda functions for deferred values (which are evaluated at time of use, to include
# dynamic step IDs or cmdline parameters)
TESTS = [
    ('POST', '/ajax/login', 
            lambda: {'email': args.user, 'password': args.password},
            lambda req: req.json().get('code') == 'success'),
    ('GET', '/manage/index', None, lambda req: 'Manage Content' in req.content),
    ('GET', '/manage/step/new', None, lambda req: 'Edit Step' in req.content),
    ('POST', '/manage/step/new', {'title': 'New Step Auto Test', 'description': 'foo'},
            lambda req: save_step(req) and ('/manage/step/' in req.url) and req.url[-1].isdigit()),
    ('GET', '/manage/slide/new', None, lambda req: 'Edit ""' in req.content),
    ('POST', '/ajax/save_slide', lambda: {
            'title': 'New Slide Auto Test', 
            'step': step, 
            'slide_type': 2,
            'description': 'Foo',
            'position': "",
            'id': 'new'
            },
            lambda req: req.json()['code'] == 'success'),
    ('GET', lambda: '/manage/stepdel/'+str(step),None, lambda req: '/manage/index' in req.url)
    ]

# Function with side-effect that saves the new step ID
def save_step(req):
    global step

    step = int(req.url.rsplit('/', 1)[-1])

    return True

def main():
    global args
    parser = argparse.ArgumentParser()
    parser.add_argument('--host')
    parser.add_argument('--port', type=int, default=80)
    parser.add_argument('--user')
    parser.add_argument('--password')
    args = parser.parse_args()

    urlroot = 'http://{0}:{1}'.format(args.host, args.port)

    session = requests.session() # provides cookie persistence
    for (method, geturl, postdata, check) in TESTS:
        # get URL value
        if callable(geturl):
            # deferred
            url = geturl()
        else:
            # static
            url = geturl

        if postdata is not None:
            if callable(postdata):
                # deferred post data
                data = postdata()
            else:
                # static post data
                data = postdata
        else:
            data = None

        # make the request
        req = session.request(method, urlroot+url, data=data)
        print method, url, req.status_code, len(req.content), step, 
        try:
            if check is not None and check(req):
                print "[OK]"
            else:
                print "[FAIL]", req.url, req.content
        except Exception as exc:
            print req.content
            break

if __name__ == '__main__':
    main()

